<?php

namespace Tests\Search;

use MercuryHolidays\Search\constants\RoomProperty;
use MercuryHolidays\Search\Searcher;
use PHPUnit\Framework\TestCase;

class SearcherTest extends TestCase
{
    /**
     * $searcher
     *
     * @var Searcher
     */
    protected Searcher $searcher;

    public function setUp(): void
    {
        $this->searcher = new Searcher();
    }

    public function testRoomPropertiesValidationWhenAddMethodIsCalled(): void
    {
        $this->expectExceptionMessage( 'One or more room properties is not correct' );

        $this->searcher->add([
            RoomProperty::AVAILABLE => true,
            RoomProperty::FLOOR     => 1,
            RoomProperty::HOTEL     => 'Hotel 1',
            RoomProperty::NUMBER    => 2,
        ]);
    }

    public function testThatOnlyAvailableRoomsAreAddedToInmemory(): void
    {
        $searcher = $this->addRoomsToSearcher( $this->searcher );

        $this->assertCount( 6, $searcher->availableRooms );
    }

    public function testThatAddedAvailableRoomsAreSortedByPrice(): void
    {
        $searcher = $this->addRoomsToSearcher( $this->searcher );

        $availableRooms = $searcher->availableRooms;

        $this->assertSame( $availableRooms[ 0 ][ RoomProperty::PRICE ], 25.80 );

        $this->assertSame( $availableRooms[ 2 ][ RoomProperty::PRICE ], 35.00 );

        $this->assertSame( $availableRooms[ 3 ][ RoomProperty::PRICE ], 45.80 );

        $this->assertSame( $availableRooms[ 5 ][ RoomProperty::PRICE ], 45.80 );
    }

    public function testIfSearchMethodReturnsEmptyArrayWhenNoRoomWasAddedInMemory(): void
    {
        $this->assertEmpty( $this->searcher->search( 2, 50, 100 ) );
    }

    public function testThatGetRoomsWithinMinimumAndMaximumBudgetMethodReturnsAppropriateRoomsBetweenBudget(): void
    {
        $searcher = $this->addRoomsToSearcher( $this->searcher );

        $availableRoomsWithinMinimumAndMaximumBudget = $searcher->getRoomsWithinMinimumAndMaximumBudget( $searcher->availableRooms, 20, 30 );

        $this->assertCount( 2, $availableRoomsWithinMinimumAndMaximumBudget );

        $this->assertSame( $availableRoomsWithinMinimumAndMaximumBudget[ 0 ][ RoomProperty::PRICE ], 25.80 );

        $this->assertSame( $availableRoomsWithinMinimumAndMaximumBudget[ 1 ][ RoomProperty::PRICE ], 25.80 );
    }

    public function testThatGetRoomWithMinimumBudgetIndexMethodReturnsRoomWithMinimumBudgetIndex(): void
    {
        $searcher = $this->addRoomsToSearcher( $this->searcher );

        $availableRoomWithMinimumBudgetIndex = $searcher->getRoomWithMinimumBudgetIndex( $searcher->availableRooms, 20 );

        $this->assertSame( $availableRoomWithMinimumBudgetIndex, 0 );
    }

    public function testThatGetRoomWithMaximumBudgetIndexMethodReturnsRoomWithMaximumBudgetIndex(): void
    {
        $searcher = $this->addRoomsToSearcher( $this->searcher );

        $availableRoomWithMaximumBudgetIndex = $searcher->getRoomWithMaximumBudgetIndex( $searcher->availableRooms, 0, 30 );

        $this->assertSame( $availableRoomWithMaximumBudgetIndex, 1 );
    }

    public function testIfSearchMethodReturnsAvailableRoomsWithinMinimumAndMaximumBudgetWhenRoomsRequiredIsLesserOrEqualsOne(): void
    {
        $searcher = $this->addRoomsToSearcher( $this->searcher );

        $availableRoomsWithinMinimumAndMaximumBudget = $searcher->getRoomsWithinMinimumAndMaximumBudget( $searcher->availableRooms, 30, 50 );

        $roomsReturnedBySearchMethod = $searcher->search( 1, 30, 50 );

        $this->assertSame( $roomsReturnedBySearchMethod, $availableRoomsWithinMinimumAndMaximumBudget );
    }

    public function testIfSearchMethodDoesNotReturnsRoomsWithinMinimumAndMaximumBudgetWhenRoomsRequiredIsGreaterThanOne(): void
    {
        $searcher = $this->addRoomsToSearcher( $this->searcher );

        $availableRoomsWithinMinimumAndMaximumBudget = $searcher->getRoomsWithinMinimumAndMaximumBudget( $searcher->availableRooms, 30, 50 );

        $roomsReturnedBySearchMethod = $searcher->search( 2, 30, 50 );

        $this->assertNotSame( $roomsReturnedBySearchMethod, $availableRoomsWithinMinimumAndMaximumBudget );
    }

    public function testIfSearchMethodReturnsRoomsAdjacentAndOnTheSameFloorWhenRoomsRequiredIsGreaterThanOne(): void
    {
        $searcher = $this->addRoomsToSearcher( $this->searcher );

        $roomsReturnedBySearchMethod = $searcher->search( 2, 30, 50 );

        $this->assertCount( 2, $roomsReturnedBySearchMethod );

        $this->assertSame( $roomsReturnedBySearchMethod[ 0 ][ RoomProperty::FLOOR ], 1 );

        $this->assertSame( $roomsReturnedBySearchMethod[ 1 ][ RoomProperty::FLOOR ], 1 );

        $this->assertSame( $roomsReturnedBySearchMethod[ 0 ][ RoomProperty::NUMBER ], 3 );

        $this->assertSame( $roomsReturnedBySearchMethod[ 1 ][ RoomProperty::NUMBER ], 4 );
    }

    public function testIfSearchMethodPassesExample1(): void
    {
        $searcher = $this->addRoomsToSearcher( $this->searcher );

        $roomsReturnedBySearchMethod = $searcher->search( 2, 20, 30 );

        $this->assertSame( $roomsReturnedBySearchMethod, [
            [ RoomProperty::HOTEL => 'Hotel A' , RoomProperty::AVAILABLE => true, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 3, RoomProperty::PRICE => 25.80 ],
            [ RoomProperty::HOTEL => 'Hotel A' , RoomProperty::AVAILABLE => true, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 4, RoomProperty::PRICE => 25.80 ]
        ] );
    }

    public function testIfSearchMethodPassesExample2(): void
    {
        $searcher = $this->addRoomsToSearcher( $this->searcher );

        $roomsReturnedBySearchMethod = $searcher->search( 2, 30, 50 );

        $this->assertSame( $roomsReturnedBySearchMethod, [
            [ RoomProperty::HOTEL => 'Hotel B' , RoomProperty::AVAILABLE => true, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 3, RoomProperty::PRICE => 45.80 ],
            [ RoomProperty::HOTEL => 'Hotel B' , RoomProperty::AVAILABLE => true, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 4, RoomProperty::PRICE => 45.80 ]
        ] );
    }

    public function testIfSearchMethodPassesExample3(): void
    {
        $searcher = $this->addRoomsToSearcher( $this->searcher );

        $roomsReturnedBySearchMethod = $searcher->search( 1, 25, 40 );

        $this->assertSame( $roomsReturnedBySearchMethod, [
            [ RoomProperty::HOTEL => 'Hotel A' , RoomProperty::AVAILABLE => true, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 3, RoomProperty::PRICE => 25.80 ],
            [ RoomProperty::HOTEL => 'Hotel A' , RoomProperty::AVAILABLE => true, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 4, RoomProperty::PRICE => 25.80 ],
            [ RoomProperty::HOTEL => 'Hotel A' , RoomProperty::AVAILABLE => true, RoomProperty::FLOOR => 2, RoomProperty::NUMBER => 7, RoomProperty::PRICE => 35.00 ]
        ] );
    }

    public function testIfGetIndexOfRoomWithMinimumRoomNumberReturnsApproriateRoomIndex(): void
    {
        $this->assertSame( 0, $this->searcher->getIndexOfRoomWithMinimumRoomNumber( $this->rooms(), 0 ) );

        $this->assertSame( 7, $this->searcher->getIndexOfRoomWithMinimumRoomNumber( $this->rooms(), 1 ) );
    }

    public function textIfGetHotelIndexMethodRemovesSpecialCharactersFromString(): void
    {
        $this->assertSame( 'hotel-a', $this->searcher->getHotelIndex( '@hotel /a*' ) );
    }

    public function textIfGetHotelIndexMethodChangesSpacesToHyphen(): void
    {
        $this->assertSame( 'hotel-a-b-also', $this->searcher->getHotelIndex( '@hotel /a* and b also' ) );
    }

    public function textIfGetHotelIndexMethodChangesAllTextToLowercase(): void
    {
        $this->assertSame( 'hotel-a-b-also', $this->searcher->getHotelIndex( '@Hotel /A* And B Also' ) );
    }

    public function rooms():array
    {
        return [
            [ RoomProperty::HOTEL => 'Hotel A' , RoomProperty::AVAILABLE => false, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 1, RoomProperty::PRICE => 25.80 ],
            [ RoomProperty::HOTEL => 'Hotel A' , RoomProperty::AVAILABLE => false, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 2, RoomProperty::PRICE => 25.80 ],
            [ RoomProperty::HOTEL => 'Hotel A' , RoomProperty::AVAILABLE => true, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 3, RoomProperty::PRICE => 25.80 ],
            [ RoomProperty::HOTEL => 'Hotel A' , RoomProperty::AVAILABLE => true, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 4, RoomProperty::PRICE => 25.80 ],
            [ RoomProperty::HOTEL => 'Hotel A' , RoomProperty::AVAILABLE => false, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 5, RoomProperty::PRICE => 25.80 ],
            [ RoomProperty::HOTEL => 'Hotel A' , RoomProperty::AVAILABLE => false, RoomProperty::FLOOR => 2, RoomProperty::NUMBER => 6, RoomProperty::PRICE => 30.10 ],
            [ RoomProperty::HOTEL => 'Hotel A' , RoomProperty::AVAILABLE => true, RoomProperty::FLOOR => 2, RoomProperty::NUMBER => 7, RoomProperty::PRICE => 35.00 ],
            [ RoomProperty::HOTEL => 'Hotel B' , RoomProperty::AVAILABLE => true, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 1, RoomProperty::PRICE => 45.80 ],
            [ RoomProperty::HOTEL => 'Hotel B' , RoomProperty::AVAILABLE => false, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 2, RoomProperty::PRICE => 45.80 ],
            [ RoomProperty::HOTEL => 'Hotel B' , RoomProperty::AVAILABLE => true, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 3, RoomProperty::PRICE => 45.80 ],
            [ RoomProperty::HOTEL => 'Hotel B' , RoomProperty::AVAILABLE => true, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 4, RoomProperty::PRICE => 45.80 ],
            [ RoomProperty::HOTEL => 'Hotel B' , RoomProperty::AVAILABLE => false, RoomProperty::FLOOR => 1, RoomProperty::NUMBER => 5, RoomProperty::PRICE => 45.80 ],
            [ RoomProperty::HOTEL => 'Hotel B' , RoomProperty::AVAILABLE => false, RoomProperty::FLOOR => 2, RoomProperty::NUMBER => 6, RoomProperty::PRICE => 49.00 ],
            [ RoomProperty::HOTEL => 'Hotel B' , RoomProperty::AVAILABLE => false, RoomProperty::FLOOR => 2, RoomProperty::NUMBER => 7, RoomProperty::PRICE => 49.00 ],
        ];
    }

    public function addRoomsToSearcher( Searcher $searcher )
    {
        foreach( $this->rooms() as $key => $room ) {
            $searcher->add( $room );
        }
        return $searcher;
    }
}
