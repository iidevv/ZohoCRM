<?php

namespace Iidev\ZohoCRM\Core\Data\Converter;

class Main
{
    public static function getFormattedDescription(string $description): string
    {
        return htmlspecialchars_decode(
            strip_tags(
                str_replace(
                    '&nbsp;',
                    ' ',
                    str_replace(
                        "\n",
                        ' ',
                        $description
                    )
                )
            )
        );
    }
}
