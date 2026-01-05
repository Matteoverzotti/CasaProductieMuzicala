<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/Constants/constants.php';
require_once __DIR__ . '/../app/Models/Artist.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Album.php';
require_once __DIR__ . '/../app/Models/Track.php';
require_once __DIR__ . '/../app/Models/Project.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class MusicCrawler {
    private const SITEMAP_URL = "https://sitemap.discogs.com/release/100.xml"; // Got it from the sitemap / robots.txt
    private false|CurlHandle $ch;

    public function __construct() {
        $this->ch = curl_init();
        curl_setopt_array($this->ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,

            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
            . 'AppleWebKit/537.36 (KHTML, like Gecko) '
            . 'Chrome/118.0.5993.90 Safari/537.36',

            CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-EN,en;q=0.9,en-US;q=0.8,en;q=0.7',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Cache-Control: max-age=0',
            ],
        ]);
    }

    public function __destruct() {
        if ($this->ch) {
            unset($this->ch);
        }
    }

    public function crawlAndPopulate(): void {
        echo "Starting Discogs music crawler...\n";

        $releaseUrls = $this->fetchSitemap();
        $musicData = $this->parseDiscogsUrls($releaseUrls);

        $successCount = 0;
        $skipCount = 0;

        foreach ($musicData as $albumData) {
            $result = $this->insertAlbum($albumData);
            if ($result === true) {
                $successCount++;
            } elseif ($result === false) {
                $skipCount++;
            }
        }

        echo "\nCrawler completed! Inserted: $successCount, Skipped: $skipCount\n";
    }

    private function fetchSitemap(): array {        
        curl_setopt($this->ch, CURLOPT_URL, self::SITEMAP_URL);
        $content = curl_exec($this->ch);
        
        if ($content === false) {
            throw new Exception("Error fetching sitemap: " . curl_error($this->ch));
        }

        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);        
        if ($httpCode !== 200) {
            throw new Exception("Sitemap returned HTTP $httpCode");
        }

        return $this->parseSitemapXml($content);
    }

    private function parseSitemapXml(string $xmlContent): array {
        $urls = [];
        
        // Extract <loc> tags containing release URLs
        preg_match_all('/<loc>(https:\/\/www\.discogs\.com\/release\/[^<]+)<\/loc>/', $xmlContent, $matches);
        
        if (!empty($matches[1])) {
            $urls = $matches[1];
        }

        echo "Parsed " . count($urls) . " release URLs from sitemap XML\n";
        return $urls;
    }

    private function parseDiscogsUrls(array $urls): array {
        $albums = [];
        // Limit to 15 releases to respect rate limits
        $selectedUrls = array_slice($urls, 0, 15);
        echo "Will fetch " . count($selectedUrls) . " releases from API\n\n";
        
        foreach ($selectedUrls as $url) {
            $albums[] = $this->parseReleaseUrl($url);
        }
        
        return $albums;
    }

    private function parseReleaseUrl(string $url): ?array {
        // URL format: https://www.discogs.com/release/20642-Radiohead-Karma-Police
        // Convert to api url: https://api.discogs.com/releases/20642
        if (!preg_match('/\/release\/(\d+)-(.+)$/', $url, $matches)) {
            return null;
        }

        $releaseId = $matches[1];
        $api_url = "https://api.discogs.com/releases/" . $releaseId;
        
        echo "[DEBUG] Fetching API: $api_url\n";
        
        curl_setopt($this->ch, CURLOPT_URL, $api_url);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($this->ch);
        
        if ($response === false) {
            echo "[DEBUG] cURL error: " . curl_error($this->ch) . "\n";
            return null;
        }
        
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            echo "[DEBUG] HTTP $httpCode for release $releaseId\n";
            return null;
        }

        $data = json_decode($response, true);
        if (!$data) {
            echo "[DEBUG] JSON parse error\n";
            return null;
        }
        
        $artist = 'Unknown Artist';
        if (!empty($data['artists'])) {
            $artist = $data['artists'][0]['name'];
            $artist = preg_replace('/\s*\(\d+\)\s*$/', '', $artist); // Remove "(2)" suffixes
        }
        
        $title = $data['title'] ?? 'Untitled';
        $year = $data['year'] ?? date('Y');        
        $genre = 'Unknown';
        if (!empty($data['styles'])) {
            $genre = $data['styles'][0];
        } elseif (!empty($data['genres'])) {
            $genre = $data['genres'][0];
        }
        
        $tracks = [];
        if (!empty($data['tracklist'])) {
            foreach ($data['tracklist'] as $track) {
                if (isset($track['type_']) && $track['type_'] === 'track') {
                    $tracks[] = [
                        'title' => $track['title'],
                        'duration' => $this->parseDuration($track['duration'] ?? '0:00'),
                    ];
                }
            }
        }
        
        echo "[DEBUG] Parsed: $artist - $title ($year) | Genre: $genre | Tracks: " . count($tracks) . "\n";
        
        // Rate limiting (Discogs allows 60 req/min unauthenticated)
        usleep(1100000);
        
        return [
            'title' => $title,
            'artist_name' => $artist,
            'release_year' => (int)$year,
            'genre' => $genre,
            'tracks' => $tracks,
            'discogs_id' => $releaseId,
            'url' => $url,
        ];
    }
    
    private function parseDuration(string $duration): int {
        if (empty($duration)) return 180;
        $parts = explode(':', $duration);
        if (count($parts) === 2) {
            return (int)$parts[0] * 60 + (int)$parts[1];
        }
        return 180;
    }

    private function insertAlbum(array $albumData): ?bool {
        try {
            $artistId = $this->getOrCreateArtist($albumData['artist_name'], $albumData['genre']);

            $album = Album::getAlbumByTitleAndArtist($albumData['title'], $artistId);
            if ($album) {
                echo "Album '{$albumData['title']}' already exists, skipping...\n";
                return false;
            }

            $album = Album::createAlbum([
                'title' => $albumData['title'],
                'artist_id' => $artistId,
                'release_year' => $albumData['release_year'],
                'genre' => $albumData['genre']
            ]);

            foreach ($albumData['tracks'] as $track)
                $this->insertTrack($album->id, $artistId, $track);

            return true;

        } catch (Exception $e) {
            echo "Error inserting album '{$albumData['title']}': " . $e->getMessage() . "\n\n";
            return null;
        }
    }

    private function getOrCreateArtist(string $name, string $genre): int {
        $artist = Artist::getArtistByStageName($name);

        if ($artist) {
            return $artist->id;
        }

        $username = strtolower(str_replace([' ', '.', ','], '_', $name));
        $email = $username . '@studio.example.com';

        $user = User::getByUsername($username);

        if (!$user) {
            $userId = User::createUser($username, $name, $email, 'artist_default_password_' . rand(1000, 9999));
        } else {
            $userId = $user->id;
        }

        return Artist::createArtist($userId, $name, 'Artist added by our crawler', null, $genre);
    }

    private function insertTrack(int $albumId, int $artistId, array $trackData): int {
        $album = Album::getAlbumById($albumId);
        if (!$album) {
            throw new Exception("Album with ID $albumId not found.");
        }
        $albumName = $album->title;
        $releaseYear = $album->release_year;
        $projectId = $this->getOrCreateReleasedProject($artistId, $albumName);

        return Track::create($projectId, $albumId, $trackData['title'], $trackData['duration'], $releaseYear);
    }


    private function getOrCreateReleasedProject(int $artistId, string $albumName): int {
        $project = Project::getProjectByAlbumName($artistId, $albumName);

        if ($project) {
            return $project->id;
        }

        return Project::create($albumName, $artistId);
    }
}

try {
    $crawler = new MusicCrawler();
    $crawler->crawlAndPopulate();
} catch (Exception $e) {
    echo "\nCrawler error: " . $e->getMessage() . "\n";
    exit(1);
}
