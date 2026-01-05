<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Album.php';
require_once __DIR__ . '/../Models/Track.php';
require_once __DIR__ . '/../Models/Artist.php';

class MusicController extends Controller {

    public function showMusic(): void {
        $albums = Album::getAllAlbums();
        $tracks = Track::getAllReleasedTracks();

        $this->render('Music/index', [
            'albums' => $albums,
            'tracks' => $tracks
        ]);
    }

    public function showAlbum(): void {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$id) {
            http_response_code(404);
            echo "Album not found";
            return;
        }

        $album = Album::getAlbumById($id);

        if (!$album) {
            http_response_code(404);
            echo "Album not found";
            return;
        }


        $tracks = Track::getTracksByAlbum($id);
        $artist = Artist::getArtistById($album->artist_id);
        $artist_name = $artist?->stage_name ?? 'Necunoscut';

        $this->render('Music/album', [
            'album' => $album,
            'tracks' => $tracks,
            'artist_name' => $artist_name
        ]);
    }
}
