<?php

namespace FME\Events\Model\Media;

interface EventConfigInterface
{

    public function getEventBaseMediaUrl();
    public function getEventBaseMediaPath();
    public function getEventMediaUrl($file);
    public function getEventMediaPath($file);
}
