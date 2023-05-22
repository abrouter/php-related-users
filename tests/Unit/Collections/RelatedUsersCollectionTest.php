<?php

declare(strict_types=1);

namespace Abrouter\RelatedUsers\Tests\Unit\Collections;

use Abrouter\RelatedUsers\Collections\RelatedUsersCollection;
use PHPUnit\Framework\TestCase as BaseTestCase;

class RelatedUsersCollectionTest extends BaseTestCase
{
    public function apply($func, array $arr): array
    {
        return array_map(function (array $val) use ($func) {
            $func($val);
            return $val;
        }, $arr);
    }

    public function testAppendingWithSingleUser()
    {
        $collection = new RelatedUsersCollection();
        $collection->append('user', 'relatedUser');

        $this->assertEquals($collection->getAllApply('sort'), $this->apply('sort', [
            'user' => ['relatedUser'],
            'relatedUser' => ['user'],
        ]));
    }

    public function testAppendingWithMultipleUsers()
    {
        $collection = new RelatedUsersCollection();
        $collection->append('user', 'relatedUser');
        $collection->append('user', 'relatedUser2');
        $collection->append('user', 'relatedUser3');

        $this->assertEquals($collection->getAllApply('sort'), $this->apply('sort', [
            'user' => ['relatedUser', 'relatedUser2', 'relatedUser3'],
            'relatedUser' => ['user', 'relatedUser2', 'relatedUser3'],
            'relatedUser2' => ['user', 'relatedUser', 'relatedUser3'],
            'relatedUser3' => ['user', 'relatedUser', 'relatedUser2'],
        ]));
    }

    public function testAppendingWithSameUsers()
    {
        $collection = new RelatedUsersCollection();
        $collection->append('user', 'relatedUser');
        $collection->append('user', 'relatedUser2');
        $collection->append('user', 'relatedUser3');
        $collection->append('relatedUser3', 'user');
        $collection->append('relatedUser2', 'user');
        $collection->append('user', 'relatedUser');
        $collection->append('relatedUser2', 'relatedUser3');
        $collection->append('relatedUser3', 'relatedUser2');

        $this->assertEquals($collection->getAllApply('sort'), $this->apply('sort', [
            'user' => ['relatedUser', 'relatedUser2', 'relatedUser3'],
            'relatedUser' => ['user', 'relatedUser2', 'relatedUser3'],
            'relatedUser2' => ['user', 'relatedUser', 'relatedUser3'],
            'relatedUser3' => ['user', 'relatedUser', 'relatedUser2'],
        ]));
    }

    public function testAppendingViaChilds()
    {
        $collection = new RelatedUsersCollection();

        $collection->append('user', 'relatedUser');
        $collection->append('relatedUser2', 'user');
        $collection->append('relatedUser3', 'user');

        $this->assertEquals($collection->getAllApply('sort'), $this->apply('sort', [
            'user' => ['relatedUser', 'relatedUser2', 'relatedUser3'],
            'relatedUser' => ['user', 'relatedUser2', 'relatedUser3'],
            'relatedUser2' => ['user', 'relatedUser', 'relatedUser3'],
            'relatedUser3' => ['user', 'relatedUser', 'relatedUser2'],
        ]));
    }

    public function testGettingForAppendingViaChilds()
    {
        $collection = new RelatedUsersCollection();

        $collection->append('user', 'relatedUser');
        $collection->append('relatedUser2', 'user');
        $collection->append('relatedUser3', 'user');

        $this->assertEquals(
            $collection->getByUserId('user'),
            ['relatedUser', 'relatedUser2', 'relatedUser3']
        );

        $this->assertEquals(
            $collection->getByUserId('relatedUser'),
            ['user', 'relatedUser2', 'relatedUser3']
        );

        $exp = ['user', 'relatedUser', 'relatedUser3'];
        sort($exp);
        $val = $collection->getByUserId('relatedUser2');
        sort($val);
        $this->assertEquals(
            $val,
            $exp
        );

        $val = $collection->getByUserId('relatedUser3');
        $exp = ['user', 'relatedUser', 'relatedUser2'];
        sort($val);
        sort($exp);

        $this->assertEquals(
            $val,
            $exp
        );
    }

    public function testAppendingWithParallelRelatedUsers()
    {
        $collection = new RelatedUsersCollection();

        $collection->append('user', 'relatedUser');
        $collection->append('relatedUser2', 'user');
        $collection->append('relatedUser3', 'user');

        $collection->append('user2', 'user2Related');
        $collection->append('user2', 'user2Related2');
        $collection->append('user2', 'user2Related3');

        $this->assertEquals($collection->getAllApply('sort'), $this->apply('sort', [
            'user' => ['relatedUser', 'relatedUser2', 'relatedUser3'],
            'relatedUser' => ['user', 'relatedUser2', 'relatedUser3'],
            'relatedUser2' => ['user', 'relatedUser', 'relatedUser3'],
            'relatedUser3' => ['user', 'relatedUser', 'relatedUser2'],

            'user2' => ['user2Related', 'user2Related2', 'user2Related3'],
            'user2Related' => ['user2', 'user2Related2', 'user2Related3'],
            'user2Related2' => ['user2', 'user2Related', 'user2Related3'],
            'user2Related3' => ['user2', 'user2Related', 'user2Related2'],
        ]));
    }

    public function testAppendingWithParallelRelatedUsersMerge()
    {
        $collection = new RelatedUsersCollection();

        $collection->append('user', 'relatedUser');
        $collection->append('relatedUser2', 'user');
        $collection->append('relatedUser3', 'user');

        $collection->append('user2', 'user2Related');
        $collection->append('user2', 'user2Related2');
        $collection->append('user2', 'user2Related3');

        $collection->append('user', 'user2');


        $this->assertEquals($collection->getAllApply('sort'), $this->apply('sort', [
            'user' => [
                'relatedUser',
                'relatedUser2',
                'relatedUser3',
                'user2',
                'user2Related',
                'user2Related2',
                'user2Related3'
            ],
            'relatedUser' => [
                'user',
                'relatedUser2',
                'relatedUser3',
                'user2',
                'user2Related',
                'user2Related2',
                'user2Related3'
            ],
            'relatedUser2' => [
                'user',
                'relatedUser',
                'relatedUser3',
                'user2',
                'user2Related',
                'user2Related2',
                'user2Related3'
            ],
            'relatedUser3' => [
                'user',
                'relatedUser',
                'relatedUser2',
                'user2',
                'user2Related',
                'user2Related2',
                'user2Related3'
            ],

            'user2' => [
                'user2Related',
                'user2Related2',
                'user2Related3',
                'user',
                'relatedUser',
                'relatedUser2',
                'relatedUser3',
            ],
            'user2Related' => [
                'user2',
                'user2Related2',
                'user2Related3',
                'user',
                'relatedUser',
                'relatedUser2',
                'relatedUser3',
            ],
            'user2Related2' => [
                'user2',
                'user2Related',
                'user2Related3',
                'user',
                'relatedUser',
                'relatedUser2',
                'relatedUser3',
            ],
            'user2Related3' => [
                'user2',
                'user2Related',
                'user2Related2',
                'user',
                'relatedUser',
                'relatedUser2',
                'relatedUser3',
            ],
        ]));
    }

    public function testAppendingWithParallelRelatedUsersMergeDirection2()
    {
        $collection = new RelatedUsersCollection();

        $collection->append('user', 'user2');

        $collection->append('user', 'relatedUser');
        $collection->append('relatedUser2', 'user');
        $collection->append('relatedUser3', 'user');

        $collection->append('user2', 'user2Related');
        $collection->append('user2', 'user2Related2');
        $collection->append('user2', 'user2Related3');

        $expected = [
            'user' => [
                'relatedUser',
                'relatedUser2',
                'relatedUser3',
                'user2',
                'user2Related',
                'user2Related2',
                'user2Related3'
            ],
            'relatedUser' => [
                'user',
                'relatedUser2',
                'relatedUser3',
                'user2',
                'user2Related',
                'user2Related2',
                'user2Related3'
            ],
            'relatedUser2' => [
                'user',
                'relatedUser',
                'relatedUser3',
                'user2',
                'user2Related',
                'user2Related2',
                'user2Related3'
            ],
            'relatedUser3' => [
                'user',
                'relatedUser',
                'relatedUser2',
                'user2',
                'user2Related',
                'user2Related2',
                'user2Related3'
            ],

            'user2' => [
                'user2Related',
                'user2Related2',
                'user2Related3',
                'user',
                'relatedUser',
                'relatedUser2',
                'relatedUser3',
            ],
            'user2Related' => [
                'user2',
                'user2Related2',
                'user2Related3',
                'user',
                'relatedUser',
                'relatedUser2',
                'relatedUser3',
            ],
            'user2Related2' => [
                'user2',
                'user2Related',
                'user2Related3',
                'user',
                'relatedUser',
                'relatedUser2',
                'relatedUser3',
            ],
            'user2Related3' => [
                'user2',
                'user2Related',
                'user2Related2',
                'user',
                'relatedUser',
                'relatedUser2',
                'relatedUser3',
            ],
        ];

        foreach ($expected as $expectedByUserId => $expectedValue) {
            $actual = $collection->getByUserId($expectedByUserId);

            sort($expectedValue);
            sort($actual);

            $this->assertEquals($expectedValue, $actual);
        }
    }
}
