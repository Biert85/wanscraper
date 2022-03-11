<?php

namespace App\Lib\SimplePie;

class Item extends \SimplePie_Item
{
    public function get_media_description(): ?string
    {
        $enclosure = $this->get_enclosure();

        return $enclosure ? $enclosure->get_description() : null;
    }

    public function get_thumbnail_url(): ?string
    {
        $thumbnail = parent::get_thumbnail();
        if ($thumbnail !== null) {
            return $thumbnail['url'];
        }

        $enclosure = $this->get_enclosure();

        return $enclosure ? $enclosure->get_thumbnail() : null;
    }
}
