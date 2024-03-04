<?php

namespace App\Services;

use Prismic\LinkResolver;
use Prismic\Dom\RichText;
use Prismic\Dom\Link;

class LinkResolverService extends LinkResolver
{
    public function resolve($link) :? string
    {
        if (property_exists($link, 'isBroken') && $link->isBroken === true) {
            return '/404';
        }
        if ($link->type === 'category') {
            return '/category/' . $link->uid;
        }
        if ($link->type === 'post') {
            return '/post/' . $link->uid;
        }
        return '/';
    }
}