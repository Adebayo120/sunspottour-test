<?php

namespace MercuryHolidays\Search\Constants;

class RoomProperty {

    public const AVAILABLE = 'available';

    public const FLOOR = 'floor';

    public const HOTEL = 'hotel';

    public const NUMBER = 'number';

    public const PRICE = 'price';

    public static function all () {
        return [
            self::AVAILABLE,
            self::FLOOR,
            self::HOTEL,
            self::NUMBER,
            self::PRICE,
        ];
    }
}