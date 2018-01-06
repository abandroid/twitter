<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;

class Client
{
    private $client;

    public function __construct(TwitterOAuth $client)
    {
        $this->client = $client;
    }

    public function getClient(): TwitterOAuth
    {
        return $this->client;
    }

    public function getTimeline(int $count = 25): array
    {
        $statuses = $this->client->get('statuses/home_timeline', ['count' => $count]);

        return $statuses;
    }

    public function postStatus(string $message, array $mediaPaths = [])
    {
        $media_ids = [];
        foreach ($mediaPaths as $mediaPath) {
            $media = $this->client->upload('media/upload', ['media' => $mediaPath]);
            $media_ids[] = $media->media_id_string;
        }

        $parameters = [
            'status' => $message,
            'media_ids' => implode(',', $media_ids)
        ];

        $response = $this->client->post('statuses/update', $parameters);

        return $response;
    }
}
