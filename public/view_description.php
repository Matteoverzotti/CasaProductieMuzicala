<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Descriere arhitectură — Casă de producție muzicală</h1>
    <h2>Scopul aplicației</h2>
    <p>Aplicația permite gestionarea activităților unei case de producție muzicală:</p>
        <ul>
            <li>administrare artiști, albume și piese muzicale;</li>
            <li>gestionare proiecte/sesiuni de înregistrare și rezervări de studio;</li>
            <li>generare rapoarte și statistici;</li>
            <li>trimitere mesaje prin formular;</li>
        </ul>
    <p>Aplicația este realizată în PHP și folosește MariaDB pentru persistarea datelor.</p>

    <h2>Roluri (Actori)</h2>
    <ul>
        <li><b>Administrator:</b> gestionare utilizatori, toate funcționalitățile CRUD, vizualizare și generare rapoarte, vizualizare statistici aplicatie, configurare feed-uri;</li>
        <li><b>Producător:</b> administrare artiști/albume/proiecte, aprobare și vizualizare rezervări, generare rapoarte specifice;</li>
        <li><b>Inginer Sunet:</b> upload și administrare fișiere audio pentru proiecte; vizualizare proiecte.</li>
        <li><b>Artist:</b> vizualizare propriilor proiecte, upload demo-uri, vizualizare status rezervări.</li>
        <li><b>Client autentificat:</b> solicitare rezervări, vizualizare status rezervări, contact.</li>
        <li><b>Vizitator neautentificat:</b> vizualizare informații publice, contact prin formular.</li>
    </ul>

    <h2>Entități principale</h2>
    <ul>
        <li><b>User</b> - contul unui utilizator. Atribute: <tt>id, username, email, password_hash, role_id</tt>.</li>
        <li><b>Rol</b> - rol asociat unui user (ex: <tt>Administrator, Producător, Inginer Sunet, Artist, Client</tt>)</li>
        <li><b>Artist</b> - artist sau trupă gestionată de casa de producție. Atribute: <tt>id, name, country, genre, bio</tt>. Conține referințe (FK) către <b>User</b>.</li>
        <li><b>Angajat</b> - angajat al casei de producție. Atribute similare cu <b>Artist</b>.</li>
        <li><b>Album</b> (sau E.P) - colecție de piese aparținând unui artist. Atribute: <tt>id, title, release_date</tt>. Conține referințe (FK) către piese și către artist/artiști.</li>
        <li><b>Piesă</b> - atribute: <tt>id, title, length, release_date, status</tt>. Conține referințe (FK) către artist/artiști și albumul din care face parte.</li>
        <li><b>Proiect</b> - proiect / sesiune de înregistrare (draft). În cadrul unui proiect se creează piese/albume. Atribute: <tt>id, title, created_by, start_date, end_date</tt>. Conține referințe către artist/artiști, ingineri de sunet și/sau producători.</li>
        <li><b>Booking</b> - rezervare de studio pentru un proiect. Atribute: <tt>id, project_id, booked_by, start_date, end_date, status</tt>.</li>
        <li><b>Mesaj</b> - mesaj trimis prin formularul de contact. Atribute: <tt>id, name, email, subject, body, sent_at</tt>.</li>
    </ul>

    <h2>Descriere a bazei de date</h2>
    <p>Aplicația web gestionează activitatea unei case de producție muzicală prin intermediul unei baze de date relaționale MariaDB. Utilizatorii site-ului pot fi de mai multe tipuri, fiecare având permisiuni diferite asupra entităților din sistem. Administratorul are control total asupra tuturor entităților, în timp ce producătorii, inginerii de sunet și artiștii au acces limitat la funcționalități specifice rolului lor. Clienții autentificați pot solicita rezervări și pot vizualiza statusul acestora, iar vizitatorii neautentificați pot accesa doar informațiile publice și formularul de contact.</p>
    <p>Un client neautentificat își poate crea un cont pentru a deveni client autentificat. Odată autentificat, acesta poate solicita fie rezervări de studio pentru proiectele sale - devenind artist, fie poate cere să devină inginer de sunet sau producător, în funcție de nevoile sale. Administratorul va aproba sau respinge aceste cereri.</p>
    <p>Un artist poate realiza unul sau mai multe booking-uri pentru a începe sau continua un proiect muzical. Fiecare booking este asociat unui proiect specific și are un status care indică dacă rezervarea este în așteptare, aprobată sau respinsă, alături de data la care s-ar desfășura. Producătorii și inginerii de sunet pot fi asociați cu proiecte pentru a colabora la înregistrări și producții muzicale. Fiecare booking este realizat de un singur artist.</p>
    <p>Un proiect (o sesiune de înregistrare) reprezintă un spațiu de lucru unde artiștii, producătorii și inginerii de sunet pot colabora pentru a crea muzică, ulterior care poate fi organizată în albume și piese. Un proiect poate include mai mulți artiști, producători și/sau ingineri de sunet, iar fiecare dintre aceștia pot lucra pe mai multe proiecte. Odată finalizate, piesele pot fi lansate publicului și pot fi organizate în albume, E.P.-uri sau single-uri (pentru simplitate, în baza de date vor fi toate grupate sub entitatea <em>Albume</em>). Fiecare piesă face parte dintr-unul sau mai multe albume (poate fi întâi lansat ca single, apoi adăugat unui album), iar fiecare album conține una sau mai multe piese.</p>
    <p>Administratorii pot genera rapoarte și statistici pentru a monitoriza activitatea casei de producție muzicală, inclusiv utilizatorii, artiștii, albumele, piesele, proiectele și rezervările.</p>

    <h2>Cod DDL pentru crearea tabelelor principale</h2>
    <pre>CREATE TABLE ROLE (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE USER (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES ROLE(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE ARTIST (
    user_id INT PRIMARY KEY,
    stage_name VARCHAR(200),
    bio TEXT,
    country VARCHAR(100),
    genre VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES USER(id) ON DELETE CASCADE
);

CREATE TABLE ANGAJAT (
    user_id INT PRIMARY KEY,
    full_name VARCHAR(200) NOT NULL,
    salary INT NOT NULL,
    hire_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_date DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES USER(id) ON DELETE CASCADE
);

CREATE TABLE PROIECT (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    created_by INT,
    start_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_date DATETIME DEFAULT NULL,
    FOREIGN KEY created_by (created_by) REFERENCES USER(id) ON DELETE SET NULL ON UPDATE CASCADE
);

-- Un proiect poate avea mai multi utilizatori asociati (artisti si/sau angajati)
CREATE TABLE PROIECT_USER (
    proiect_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (proiect_id, user_id),
    FOREIGN KEY (proiect_id) REFERENCES PROIECT(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES USER(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE BOOKING (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proiect_id INT NOT NULL,
    booked_by INT NOT NULL,
    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    FOREIGN KEY (proiect_id) REFERENCES PROIECT(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (booked_by) REFERENCES USER(id) ON DELETE CASCADE ON UPDATE CASCADE
);


CREATE TABLE ALBUM (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    artist_id INT NOT NULL,
    release_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    genre VARCHAR(100),
    FOREIGN KEY (artist_id) REFERENCES ARTIST(user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE PIESA (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proiect_id INT NOT NULL,
    album_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    duration INT NOT NULL, -- durata in secunde
    release_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('draft','released','archived') DEFAULT 'draft',
    FOREIGN KEY (proiect_id) REFERENCES PROIECT(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (album_id) REFERENCES ALBUM(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE MESAJ (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_name VARCHAR(150) NOT NULL,
    sender_email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
</pre>

    <h2>Diagrama ER</h2>
    <h3>Generată folosind DataGrip. Powered by graphviz</h3>
    <img src="diagrama.svg" alt="Diagrama ER a bazei de date">
</body>
</html>