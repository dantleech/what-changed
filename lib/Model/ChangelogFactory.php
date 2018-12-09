<?php

namespace DTL\WhatChanged\Model;

interface ChangelogFactory
{
    public function changeLogFor(PackageHistory $history);
}
