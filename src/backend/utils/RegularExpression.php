<?php
//Añadimos al psr-4
namespace App\backend\utils;

interface RegularExpression{
    public function extractRegex(string $pathResource): array;
    // public function createRegex(): string;
}

?>