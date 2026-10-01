<?php

namespace App\Http\Controllers;

/**
 * Reviewing the documents a holder has uploaded.
 *
 * Client bug report items 24 and 25: an admin can already DEFINE which
 * documents each role must supply, and providers are already uploading them -
 * three `documents_verify` records existed on 1 October - but there was
 * nowhere in this panel to look at one and approve or refuse it.
 *
 * Drivers, vendors and owners each have their own near-identical pair of
 * screens (`drivers/document_list`, `owners/documentIndex`, …). Rather than add
 * a fourth and fifth copy, this controller serves ONE view parameterised by
 * role. The three existing screens are deliberately left alone - they are live
 * and they work - but they can be pointed at this view later without rewriting
 * anything.
 *
 * The role map mirrors `documentTargetFor()` in documents/create.blade.php.
 * KEEP THE TWO IN STEP: if a role is added there, add it here.
 */
class DocumentReviewController extends Controller
{
    /**
     * Where each role's holders live, and what approving every document does
     * to them.
     *
     * `activates` is false for both roles here on purpose. Only a DRIVER is
     * switched on and off by their documents - that is the existing behaviour
     * in drivers/document_list, and extending it to providers would silently
     * deactivate people the admin never chose to deactivate.
     */
    private const ROLES = [
        'provider' => [
            'collection' => 'users',
            'activates'  => false,
            'title_key'  => 'provider_document_details',
            'back_route' => 'providers',
            'back_key'   => 'provider_plural',
        ],
        'worker' => [
            'collection' => 'providers_workers',
            'activates'  => false,
            'title_key'  => 'worker_document_details',
            'back_route' => 'ondemand.workers.index',
            'back_key'   => 'worker_plural',
        ],
    ];

    public function index($role, $id)
    {
        /* An unknown role is a 404 rather than a blank screen: it means a link
         * was built wrongly, and guessing which collection to read would be
         * worse than saying so. */
        if (!isset(self::ROLES[$role])) {
            abort(404);
        }

        return view('documents.holder_list', [
            'id'     => $id,
            'role'   => $role,
            'config' => self::ROLES[$role],
        ]);
    }
}
