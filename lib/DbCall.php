<?php

abstract class DbCall
{
    public function __construct(protected readonly mysqli $mysqli) {}
}