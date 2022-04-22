<?php

namespace MercuryHolidays\Search;

use MercuryHolidays\Search\Constants\RoomProperty;

class Searcher
{

    /**
     * $availableRooms
     *
     * @var array
     */
    public array $availableRooms = [];

    /**
     * add
     *
     * @param array $property
     * @return void
     */
    public function add( array $property ): void
    {
        $this->validateRoomProperties( $property );

        if( !$property[ RoomProperty::AVAILABLE ] ) {
            return;
        }

        if( !count( $this->availableRooms ) ) {
            $this->availableRooms[] = $property;
            return;
        }

        $this->sortRoomsAndInsert( $this->availableRooms, $property );
    }

    /**
     * search
     *
     * @param integer $roomsRequired
     * @param integer $minimum
     * @param integer $maximum
     * @return array
     */
    public function search( int $roomsRequired, int $minimum, int $maximum ): array
    {
        if( !count( $this->availableRooms ) ) {
            return [];
        }

        $roomsWithinMinimumAndMaximumBudget = $this->getRoomsWithinMinimumAndMaximumBudget( $this->availableRooms, $minimum, $maximum );

        if( $roomsRequired <= 1 ) {
            return $roomsWithinMinimumAndMaximumBudget;
        }

        return $this->getRoomsAdjacentAndOnTheSameFloor( $roomsWithinMinimumAndMaximumBudget, $roomsRequired );
    }

    /**
     * getRoomsWithinMinimumAndMaximumBudget
     *
     * @param array $rooms
     * @param integer $minimumPrice
     * @param integer $maximumPrice
     * @return array
     */
    public function getRoomsWithinMinimumAndMaximumBudget( array $rooms, int $minimumPrice, int $maximumPrice ): array
    {
        $roomWithMinimumBudgetIndex = $this->getRoomWithMinimumBudgetIndex( $rooms, $minimumPrice );

        $roomWithMaximumBudgetIndex = $this->getRoomWithMaximumBudgetIndex( $rooms, $roomWithMinimumBudgetIndex, $maximumPrice );

        $length = ( $roomWithMaximumBudgetIndex - $roomWithMinimumBudgetIndex ) + 1;
    
        return array_slice( $rooms, $roomWithMinimumBudgetIndex, $length );
    }

    /**
     * getRoomWithMinimumBudgetIndex
     *
     * @param array $rooms
     * @param integer $minimumPrice
     * @return integer
     */
    public function getRoomWithMinimumBudgetIndex( array $rooms, int $minimumPrice ): int
    {
        $roomWithMinimumBudgetIndex = 0;

        $roomWithMaximumBudgetIndex = count( $rooms ) - 1;

        while( $roomWithMinimumBudgetIndex < $roomWithMaximumBudgetIndex ) {

            $midIndex = floor( ( $roomWithMinimumBudgetIndex + $roomWithMaximumBudgetIndex ) / 2 );

            if( $minimumPrice <= $rooms[ $midIndex ][ RoomProperty::PRICE ] ) {
                $roomWithMaximumBudgetIndex = $midIndex;
            } else {
                $roomWithMinimumBudgetIndex = $midIndex + 1;
            }
        }

        return $roomWithMinimumBudgetIndex;
    }

    /**
     * getRoomWithMaximumBudgetIndex
     *
     * @param array $rooms
     * @param integer $roomWithMinimumBudgetIndex
     * @param integer $maximumPrice
     * @return integer
     */
    public function getRoomWithMaximumBudgetIndex( array $rooms, int $roomWithMinimumBudgetIndex, int $maximumPrice ): int
    {
        $roomWithMaximumBudgetIndex = count( $rooms ) - 1;

        while( $roomWithMinimumBudgetIndex < $roomWithMaximumBudgetIndex ) {

            $midIndex = floor( ( $roomWithMinimumBudgetIndex + $roomWithMaximumBudgetIndex ) / 2 + 1 );

            if( $maximumPrice < $rooms[ $midIndex ][ RoomProperty::PRICE ] ) {
                $roomWithMaximumBudgetIndex = $midIndex  -1;
            } else {
                $roomWithMinimumBudgetIndex = $midIndex;
            }
        }

        return $roomWithMaximumBudgetIndex;
    }

    /**
     * getRoomsAdjacentAndOnTheSameFloor
     *
     * @param array $rooms
     * @param integer $roomsRequired
     * @return array
     */
    public function getRoomsAdjacentAndOnTheSameFloor( array $rooms, int $roomsRequired ): array
    {
        $result = [];

        $arrayOfAdjacentRoomsGroupedBasedOnHotelNamesAndFloors = [];

        for( $roomIndex = 0; $roomIndex < count( $rooms ); $roomIndex++ ) { 

            $indexOfRoomWithMinimumRoomNumber = $this->getIndexOfRoomWithMinimumRoomNumber( $rooms, $roomIndex );

            if( $roomIndex !== $indexOfRoomWithMinimumRoomNumber )
            {
                $lesser = $rooms[ $indexOfRoomWithMinimumRoomNumber ];
    
                $rooms[ $indexOfRoomWithMinimumRoomNumber ] = $rooms[ $roomIndex ];
    
                $rooms[ $roomIndex ] = $lesser;
            }
            
            $room = $rooms[ $roomIndex ];

            $hotelIndex = $this->getHotelIndex( $room[ RoomProperty::HOTEL ] );

            $floorIndex = $room[ RoomProperty::FLOOR ];

            if( !isset( $arrayOfAdjacentRoomsGroupedBasedOnHotelNamesAndFloors[ $hotelIndex ] ) ) {
                $arrayOfAdjacentRoomsGroupedBasedOnHotelNamesAndFloors[ $hotelIndex ] = [];
            }

            if( !isset( $arrayOfAdjacentRoomsGroupedBasedOnHotelNamesAndFloors[ $hotelIndex ][ $floorIndex ] ) ) {
                $arrayOfAdjacentRoomsGroupedBasedOnHotelNamesAndFloors[ $hotelIndex ][ $floorIndex ] = [];
            }

            if( !count( $arrayOfAdjacentRoomsGroupedBasedOnHotelNamesAndFloors[ $hotelIndex ][ $floorIndex ] ) ) {
                $arrayOfAdjacentRoomsGroupedBasedOnHotelNamesAndFloors[ $hotelIndex ][ $floorIndex ] = [ $room ];
                continue;
            }

            $floorRooms = $arrayOfAdjacentRoomsGroupedBasedOnHotelNamesAndFloors[ $hotelIndex ][ $floorIndex ];
            
            $floorLastRoomAdjacentRoomNumber = $floorRooms[ count( $floorRooms ) - 1 ][ RoomProperty::NUMBER ] + 1;

            if( $floorLastRoomAdjacentRoomNumber == $room[ RoomProperty::NUMBER ] ) {
                $arrayOfAdjacentRoomsGroupedBasedOnHotelNamesAndFloors[ $hotelIndex ][ $floorIndex ][] = $room;
            } else {
                $arrayOfAdjacentRoomsGroupedBasedOnHotelNamesAndFloors[ $hotelIndex ][ $floorIndex ] = [ $room ];
            }

            if( count( $arrayOfAdjacentRoomsGroupedBasedOnHotelNamesAndFloors[ $hotelIndex ][ $floorIndex ] ) == $roomsRequired ) {

                $floorRooms = $arrayOfAdjacentRoomsGroupedBasedOnHotelNamesAndFloors[ $hotelIndex ][ $floorIndex ];

                $result = [ ...$result, ...$floorRooms ];

                $arrayOfAdjacentRoomsGroupedBasedOnHotelNamesAndFloors[ $hotelIndex ][ $floorIndex ] = [];
            }
        }

        return $result;
    }

    /**
     * getIndexOfRoomWithMinimumRoomNumber
     *
     * @param array $rooms
     * @param integer $roomIndex
     * @return integer
     */
    public function getIndexOfRoomWithMinimumRoomNumber( array $rooms, int $roomIndex ): int
    {
        $indexOfRoomWithMinimumRoomNumber = $roomIndex;

        for( $compareToRoomIndex = $roomIndex + 1; $compareToRoomIndex < count( $rooms ); $compareToRoomIndex++ ) { 

            if( $rooms[ $compareToRoomIndex ][ RoomProperty::NUMBER ] < $rooms[ $indexOfRoomWithMinimumRoomNumber ][ RoomProperty::NUMBER ] ) {
                $indexOfRoomWithMinimumRoomNumber = $compareToRoomIndex;
            }

        }

        return $indexOfRoomWithMinimumRoomNumber;
    }

    /**
     * validateRoomProperties
     *
     * @param array $room
     * @return void
     */
    public function validateRoomProperties( array $room ): void
    {
        $roomProperties = array_keys( $room );

        sort( $roomProperties );

        if( RoomProperty::all() !== $roomProperties ) {
            throw new \Exception( 'One or more room properties is not correct' );
        }
    }

    /**
     * sortRoomsAndInsert
     *
     * @param array $rooms
     * @param array $room
     * @return void
     */
    public function sortRoomsAndInsert( array $rooms, array $room ): void
    {
        for(    $i = count( $rooms ) - 1; 
                ( $i >= 0 && $rooms[ $i ][ RoomProperty::PRICE ] > $room[ RoomProperty::PRICE ] ); 
                $i-- 
        ) { 
            $rooms[ $i + 1 ] = $rooms[ $i ];
        }

        $rooms[ $i + 1 ] = $room;

        $this->availableRooms = $rooms;
    }

    /**
     * getHotelIndex
     *
     * @param string $hotelName
     * @return string
     */
    public function getHotelIndex( string $hotelName ): string
    {
        $hotelNameInLowerCaseWithRemovedSpaces = strtolower( str_replace( ' ', '-', $hotelName ) );

        return preg_replace( '/[^A-Za-z0-9\-]/', '', $hotelNameInLowerCaseWithRemovedSpaces );
    }
}
