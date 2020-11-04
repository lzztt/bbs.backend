<?php

declare(strict_types=1);

namespace site;

class City
{
    public const HOUSTON = 1;
    public const DALLAS = 2;
    public const SFBAY = 4;

    public int $id;
    public string $domain;
    public string $nameEn;
    public string $nameZh;
    public int $tidForum;
    public int $tidYp;
}
