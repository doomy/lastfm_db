<?php
final readonly class ApiClient
{
    const API_KEY = '523fe5f6eb50aaf813e6bfa4e9b9fb23';

    /** @return string[] */
    public function getArtists(string $username): array
    {
        $page = 1;

        $artists = [];

        do {
            $url = sprintf(
                "http://ws.audioscrobbler.com/2.0/?method=user.gettopartists&user=%s&api_key=%s&format=json&limit=1000&page=%d",
                $username,
                self::API_KEY,
                $page
            );
            $rawData = file_get_contents($url);
            $data = json_decode($rawData, true);
            $totalPages = (int) $data['topartists']['@attr']['totalPages'];

            foreach ($data['topartists']['artist'] as $artistData) {
                $url = $artistData['url'];
                preg_match('/https:\/\/www\.last\.fm\/music\/(.+)/', $url, $matches);
                $artists[] = $matches[1];
            }

            $page++;
        } while ($page <= $totalPages);

        return $artists;

    }


}