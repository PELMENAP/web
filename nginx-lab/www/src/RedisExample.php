<?php

namespace App;

use App\Helpers\ClientFactory;

class RedisExample
{
    private $client;

    public function __construct()
    {
        $this->client = ClientFactory::make('http://webdis:7379/');
    }

    public function addUser($userId, $userData)
    {
        $jsonData = json_encode($userData);
        $response = $this->client->get("SET/user:$userId/$jsonData");
        return $response->getBody()->getContents();
    }

    public function getUser($userId)
    {
        $response = $this->client->get("GET/user:$userId");
        $content = $response->getBody()->getContents();
        $decoded = json_decode($content, true);
        
        if (isset($decoded['GET'])) {
            return json_decode($decoded['GET'], true);
        }
        return null;
    }

    public function deleteUser($userId)
    {
        $response = $this->client->get("DEL/user:$userId");
        return $response->getBody()->getContents();
    }

    public function getAllUserIds()
    {
        $response = $this->client->get("KEYS/user:*");
        $content = $response->getBody()->getContents();
        $decoded = json_decode($content, true);
        return $decoded['KEYS'] ?? [];
    }

    public function getUserCount()
    {
        $keys = $this->getAllUserIds();
        return count($keys);
    }

    public function incrementUserLogins($userId)
    {
        $response = $this->client->get("INCR/user:$userId:logins");
        $content = $response->getBody()->getContents();
        $decoded = json_decode($content, true);
        return $decoded['INCR'] ?? 0;
    }

    public function getUserLogins($userId)
    {
        $response = $this->client->get("GET/user:$userId:logins");
        $content = $response->getBody()->getContents();
        $decoded = json_decode($content, true);
        return isset($decoded['GET']) ? (int)$decoded['GET'] : 0;
    }

    public function addUserToSet($setName, $userId)
    {
        $response = $this->client->get("SADD/$setName/$userId");
        return $response->getBody()->getContents();
    }

    public function getUsersFromSet($setName)
    {
        $response = $this->client->get("SMEMBERS/$setName");
        $content = $response->getBody()->getContents();
        $decoded = json_decode($content, true);
        return $decoded['SMEMBERS'] ?? [];
    }

    public function setUserExpire($userId, $seconds)
    {
        $response = $this->client->get("EXPIRE/user:$userId/$seconds");
        return $response->getBody()->getContents();
    }
}