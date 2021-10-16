<?php

/**
 * conjoon
 * php-ms-imapuser
 * Copyright (C) 2019-2021 Thorsten Suckow-Homberg https://github.com/conjoon/php-ms-imapuser
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Tests\App\Http\V0\Controllers;

use App\Http\V0\Controllers\UserController;
use Conjoon\Illuminate\Auth\Imap\DefaultImapUserProvider;
use Conjoon\Illuminate\Auth\Imap\ImapUser;
use Conjoon\Illuminate\Auth\Imap\ImapUserProvider;
use Conjoon\Mail\Client\Data\MailAccount;
use Tests\TestCase;

/**
 * Class UserControllerTest
 * @package Tests\App\Http\V0\Controllers
 */
class UserControllerTest extends TestCase
{
    /**
     * Tests authenticate() to make sure method either returns an imap user or not.
     *
     * @return void
     *
     * @noinspection PhpUndefinedMethodInspection
     */
    public function testAuthenticate()
    {
        $endpoint = $this->getImapUserEndpoint("auth", "v0");


        $repository = $this->getMockBuilder(DefaultImapUserProvider::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->app->when(UserController::class)
              ->needs(ImapUserProvider::class)
              ->give(function () use ($repository) {
                 return $repository;
              });

        $repository->expects($this->exactly(2))
                   ->method("getUser")
                   ->will(
                       $this->onConsecutiveCalls(
                           null,
                           new ImapUser("foo", "bar", new MailAccount([]))
                       )
                   );

        $response = $this->call(
            "POST",
            $endpoint,
            ["username" => "dev@conjoon.org", "password" => "test"]
        );

        $this->assertEquals(401, $response->status());
        $this->assertEquals(
            ["success" => false, "msg" => "Unauthorized.", "status" => 401],
            $response->getData(true)
        );


        $response = $this->call(
            "POST",
            $endpoint,
            ["username" => "user", "password" => "test"]
        );
        $this->assertEquals(200, $response->status());
        $this->assertEquals(
            ["success" => true, "data" => ["username" => "foo", "password" => "bar"]],
            $response->getData(true)
        );
    }
}
