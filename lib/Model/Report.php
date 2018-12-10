<?php

namespace DTL\WhatChanged\Model;

interface Report
{
    public function render(ReportOutput $output, ReportOptions $options): void;
}
