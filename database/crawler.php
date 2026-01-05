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

    public function crawlAndPopulate(): void {
        echo "🎵 Starting music crawler...\n";
        echo str_repeat("=", 60) . "\n\n";

        // Get music data (in production, this would scrape a website)
        $musicData = $this->fetchMusicData();

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

        echo "\n" . str_repeat("=", 60) . "\n";
        echo "✅ Crawler completed successfully!\n";
        echo "📊 Statistics:\n";
        echo "   - Albums added: $successCount\n";
        echo "   - Albums skipped (already exist): $skipCount\n";
        echo "   - Total processed: " . count($musicData) . "\n";
    }

    private function fetchMusicData(): array {
        echo "📡 Fetching music data...\n\n";

        // Sample data representing popular tracks/albums
        // Replace this with actual scraping logic
        return [
            [
                'title' => 'Greatest Hits Vol. 1',
                'artist_name' => 'The Studio Artists',
                'release_date' => '2024-01-15',
                'genre' => 'Pop',
                'tracks' => [
                    ['title' => 'Summer Vibes', 'duration' => 245],
                    ['title' => 'Night Drive', 'duration' => 198],
                    ['title' => 'City Lights', 'duration' => 223],
                    ['title' => 'Dancing Stars', 'duration' => 210],
                ]
            ],
            [
                'title' => 'Electronic Dreams',
                'artist_name' => 'DJ Producer',
                'release_date' => '2024-02-20',
                'genre' => 'Electronic',
                'tracks' => [
                    ['title' => 'Pulse', 'duration' => 267],
                    ['title' => 'Midnight Echo', 'duration' => 312],
                    ['title' => 'Neon Waves', 'duration' => 289],
                    ['title' => 'Digital Sunrise', 'duration' => 256],
                    ['title' => 'Cyber Dreams', 'duration' => 301],
                ]
            ],
            [
                'title' => 'Acoustic Sessions',
                'artist_name' => 'The Songwriters',
                'release_date' => '2023-12-10',
                'genre' => 'Acoustic',
                'tracks' => [
                    ['title' => 'Whispers', 'duration' => 201],
                    ['title' => 'Sunrise', 'duration' => 234],
                    ['title' => 'Memories', 'duration' => 189],
                ]
            ],
            [
                'title' => 'Urban Beats',
                'artist_name' => 'MC Flow',
                'release_date' => '2024-03-05',
                'genre' => 'Hip-Hop',
                'tracks' => [
                    ['title' => 'Street Stories', 'duration' => 278],
                    ['title' => 'City Rhythm', 'duration' => 245],
                    ['title' => 'Late Night', 'duration' => 298],
                    ['title' => 'Concrete Jungle', 'duration' => 265],
                ]
            ],
            [
                'title' => 'Rock Anthems',
                'artist_name' => 'The Rockers',
                'release_date' => '2023-11-20',
                'genre' => 'Rock',
                'tracks' => [
                    ['title' => 'Thunder Road', 'duration' => 312],
                    ['title' => 'Rebel Heart', 'duration' => 289],
                    ['title' => 'Fire Storm', 'duration' => 334],
                    ['title' => 'Electric Soul', 'duration' => 298],
                    ['title' => 'Final Stand', 'duration' => 356],
                ]
            ],
            [
                'title' => 'Jazz Nights',
                'artist_name' => 'Smooth Jazz Collective',
                'release_date' => '2024-01-30',
                'genre' => 'Jazz',
                'tracks' => [
                    ['title' => 'Blue Moon', 'duration' => 412],
                    ['title' => 'Velvet Lounge', 'duration' => 367],
                    ['title' => 'Midnight Sax', 'duration' => 389],
                ]
            ],
        ];
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
                'release_date' => $albumData['release_date'],
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
        $releaseDate = $album->release_date;
        $projectId = $this->getOrCreateReleasedProject($artistId, $albumName);

        return Track::create($projectId, $albumId, $trackData['title'], $trackData['duration'], $releaseDate);
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
    echo "\n❌ Fatal crawler error: " . $e->getMessage() . "\n";
    exit(1);
}
