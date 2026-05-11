<?php
$pdo = new PDO('mysql:host=localhost;dbname=rps_db', 'root', '');
$stmt = $pdo->query('SELECT mk.nama_mk, mk.dosen_id, u.nama_lengkap as user_name, d.nama as dosen_name 
                    FROM mata_kuliah mk 
                    LEFT JOIN users u ON mk.dosen_id = u.id 
                    LEFT JOIN dosen d ON mk.dosen_id = d.id 
                    LIMIT 5');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($results);
