<?php

namespace Pheasant\Tests\Relationships;

use \Pheasant\Tests\Examples\Hero;
use \Pheasant\Tests\Examples\Power;
use \Pheasant\Tests\Examples\SecretIdentity;

class IncludesTest extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $migrator = new \Pheasant\Migrate\Migrator();
        $migrator
            ->create('hero', Hero::schema())
            ->create('power', Power::schema())
            ->create('secretidentity', SecretIdentity::schema())
            ;

        $spiderman = Hero::createHelper('Spider Man', 'Peter Parker', array(
            'Super-human Strength', 'Spider Senses'
        ));
        $superman = Hero::createHelper('Super Man', 'Clark Kent', array(
            'Super-human Strength', 'Invulnerability'
        ));
        $batman = Hero::createHelper('Batman', 'Bruce Wayne', array(
            'Richness', 'Super-human Intellect'
        ));
    }

    public function testIncludesHitsCache()
    {
        $queries = 0;

        $this->connection()->filterChain()->onQuery(function ($sql) use (&$queries) {
            ++$queries;

            return $sql;
        });

        // the first lookup of SecretIdentity should cache all the rest
        $heros = Hero::all()->includes(array('SecretIdentity'))->toArray();
        $this->assertNotNull($heros[0]->SecretIdentity);

        // these should be from cache
        $queries = 0;
        $this->assertNotNull($heros[1]->SecretIdentity);
        $this->assertNotNull($heros[2]->SecretIdentity);

        $this->assertEquals(0, $queries, "this should have hit the cache");
    }
}
