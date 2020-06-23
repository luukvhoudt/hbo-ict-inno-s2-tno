<?php

namespace TNO\ContactForm7\Tests\Applications;

use TNO\ContactForm7\Applications\Plugin;
use TNO\ContactForm7\Tests\TestCase;

class PluginTest extends TestCase
{
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new Plugin('name', 'namespace', __DIR__);
    }

    /** @test */
    public function name_is_set_correctly()
    {
        $this->assertEquals('name', $this->subject->getName());
    }

    /** @test */
    public function namespace_is_set_correctly()
    {
        $this->assertEquals('namespace', $this->subject->getNamespace());
    }

    /** @test */
    public function app_directory_is_set_correctly()
    {
        $this->assertEquals(__DIR__, $this->subject->getAppDir());
    }
}
