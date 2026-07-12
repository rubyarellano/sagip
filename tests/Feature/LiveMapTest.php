<?php

use App\Enums\UserRole;
use App\Models\User;
use Livewire\Livewire;

test('admin can render live map component', function () {
    config(['app.debug' => false]);

    $user = User::factory()->create(['role' => UserRole::Admin->value]);
    $this->actingAs($user);

    $html = Livewire::test('pages::admin.live-map')->html();
    file_put_contents('live-map-render.html', $html);

    $dom = new DOMDocument;
    $dom->loadHTML($html, LIBXML_NOERROR);
    $body = $dom->getElementsByTagName('body')->item(0);

    $nodes = [];
    foreach ($body->childNodes as $child) {
        if ($child->nodeType == XML_ELEMENT_NODE) {
            $nodes[] = $child->tagName.' (class: '.$child->getAttribute('class').')';
        }
    }
    file_put_contents('live-map-nodes.txt', implode("\n", $nodes));
});
