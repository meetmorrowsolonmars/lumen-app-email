<?php
/**
 * conjoon
 * php-cn_imapuser
 * Copyright (C) 2019 Thorsten Suckow-Homberg https://github.com/conjoon/php-cn_imapuser
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
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Imap\Service\MailFolderService;

use Auth;



/**
 * Class MailFolderController
 * @package App\Http\Controllers
 */
class MailFolderController extends Controller {


    /**
     * @var MailFolderService
     */
    protected $mailFolderService;


    /**
     * MailFolderController constructor.
     *
     * @param MailFolderService $mailFolderService
     */
    public function __construct(MailFolderService $mailFolderService) {

        $this->mailFolderService = $mailFolderService;

    }


    /**
     * Returns all available MailFolders for the user that is currently
     * authenticated for the specified $mailAccountId.
     *
     * @return ResponseJson
     *
     * @throws if $mailAccountId is not the id of the ImapAccount associated
     * with the user
     */
    public function get($mailAccountId) {

        $user = Auth::user();

        $account = $user->getImapAccount();

        if ($account->getId() !== $mailAccountId) {
            throw new \Illuminate\Auth\Access\AuthorizationException();
        }

        return response()->json([
            "success" => true,
            "data" => $this->mailFolderService->getMailFoldersFor($user->getImapAccount())
        ]);

    }

}
