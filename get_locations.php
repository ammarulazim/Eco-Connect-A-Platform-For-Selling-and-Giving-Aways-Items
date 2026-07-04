<?php
header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$matches = [];

if (strlen($query) >= 2) {
    // 🌐 Query OpenStreetMap Nominatim
    // countrycodes=my limits the query strictly within Malaysia
    $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($query) . "&countrycodes=my&format=json&limit=10";

    // OpenStreetMap requires a custom User-Agent header to prevent blocking
    $options = [
        'http' => [
            'header' => "User-Agent: EcoConnectApp/1.0 (contact@yourdomain.com)\r\n"
        ]
    ];
    
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    if ($response !== false) {
        $data = json_decode($response, true);
        
        if (is_array($data)) {
            foreach ($data as $place) {
                // 'display_name' contains the complete, comma-separated location details
                if (isset($place['display_name'])) {
                    $full_address = $place['display_name'];
                    
                    // Optional: Clean up trailing country text if it feels redundant
                    $full_address = str_replace(", Malaysia", "", $full_address);
                    
                    $matches[] = trim($full_address);
                }
            }
            
            // Filter out any duplicate strings
            $matches = array_unique($matches);
            $matches = array_values($matches);
        }
    }
}

// Return the top 7 matching location strings to script.js
echo json_encode(array_slice($matches, 0, 7));