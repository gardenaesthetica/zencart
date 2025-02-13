<?php

namespace Tests\Browser;


use Laravel\Dusk\Browser;
use Tests\Browser\Traits\ConfigureFileConcerns;
use Tests\Browser\Traits\DatabaseConcerns;

abstract class InstallDuskTestCase extends DuskTestCase
{
    use ConfigureFileConcerns;
    use DatabaseConcerns;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadDuskConfigure();
        $this->makeEmptyConfigures(DIR_FS_ROOT);
        $this->createDatabase();
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
    }
}
