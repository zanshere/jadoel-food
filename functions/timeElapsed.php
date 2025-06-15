<?php 
function time_elapsed_string($datetime, $full = false) {
    try {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        // Hitung komponen waktu
        $weeks = floor($diff->d / 7);
        $days = $diff->d % 7;
        $hours = $diff->h;
        $minutes = $diff->i;
        $seconds = $diff->s;
        
        // Buat array dengan nilai yang sudah dihitung
        $time_components = [
            'y' => $diff->y,
            'm' => $diff->m,
            'w' => $weeks,
            'd' => $days,
            'h' => $hours,
            'i' => $minutes,
            's' => $seconds
        ];
        
        $string = [
            'y' => 'tahun',
            'm' => 'bulan',
            'w' => 'minggu',
            'd' => 'hari',
            'h' => 'jam',
            'i' => 'menit',
            's' => 'detik',
        ];
        
        $result = [];
        foreach ($string as $key => $text) {
            if ($time_components[$key] > 0) {
                $result[] = $time_components[$key] . ' ' . $text;
            }
        }

        if (!$full && !empty($result)) {
            $result = [reset($result)]; // Ambil elemen pertama saja
        }
        
        return $result ? implode(', ', $result) . ' yang lalu' : 'baru saja';
    } catch (Exception $e) {
        error_log("Error in time_elapsed_string: " . $e->getMessage());
        return 'baru saja';
    }
}
?>