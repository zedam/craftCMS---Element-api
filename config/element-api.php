<?php
/**
 * Created by PhpStorm.
 * User: zedam
 * Date: 06/02/2018
 * Time: 23:44
 */

use craft\elements\Entry;
use craft\elements\ElementType;
use craft\helpers\UrlHelper;
use craft\elements\Asset;
use craft\elements\MatrixBlock;

return [
  'endpoints' => [
    /*'api/directors.json' => [
      'elementType' => Entry::class,
      'criteria' => ['section' => 'directors'],
      'transformer' => function (Entry $entry) {
        $object = getItem($entry);
        return $object;
      },
    ],
    'api/directors/<entryId:\d+>.json' => function ($entryId) {
      return [
        'elementType' => Entry::class,
        'criteria' => ['section' => 'directors', 'id' => $entryId],
        'one' => TRUE,
        'transformer' => function (Entry $entry) {
          $object = getItem($entry);
          return $object;
        },
      ];
    },

    //projects
    'api/projects.json' => [
      'elementType' => Entry::class,
      'criteria' => ['section' => 'projects'],
      'transformer' => function (Entry $entry) {
        $object = getItem($entry);
        return $object;
      },
    ],
    'api/projects/<entryId:\d+>.json' => function ($entryId) {
      return [
        'elementType' => Entry::class,
        'criteria' => ['section' => 'projects', 'id' => $entryId],
        'one' => TRUE,
        'transformer' => function (Entry $entry) {
          $object = getItem($entry);
          return $object;
        },
      ];
    },*/

    //projects
    'api/<slug:{slug}>.json' => function ($slug) {
      return [
        'elementType' => Entry::class,
        'criteria' => ['section' => $slug],
        'transformer' => function (Entry $entry) {
          $object = getItem($entry);
          return $object;
        },
      ];
    },
    'api/<slug:{slug}>/<entryId:\d+>.json' => function ($slug, $entryId) {
      return [
        'elementType' => Entry::class,
        'criteria' => ['section' => $slug, 'id' => $entryId],
        'one' => TRUE,
        'transformer' => function (Entry $entry) {
          $object = getItem($entry);
          return $object;
        },
      ];
    },

    //projects
    'api/pages/<slug:{slug}>.json' => function ($slug) {
      return [
        'elementType' => Entry::class,
        'pageParam' => 'pg',
        'criteria' => ['section' => $slug],
        'transformer' => function (Entry $entry) {
          $object = getItem($entry);
          return $object;
        },
      ];
    },
  ]
];

function getItem ($entry) {
  $highlight= $entry->highlight;
  $handle = $entry->getSection()->handle;
  $photos = getPhotos($entry, $handle);
  $blocks = getBlocks($entry);

  $tags = getTags($entry);

  if ($entry->id) {
    $object['id'] = $entry->id;
  }
  if ($handle) {
    $object['handle'] = $handle;
  }
  if ($highlight) {
    $object['highlight'] = $highlight;
  }
  if ($entry->title) {
    $object['title'] = $entry->title;
  }

  if ($entry->headline) {
    $object['headline'] = $entry->headline;
  }
  if ($entry->metaTitle) {
    $object['metaTitle'] = $entry->metaTitle;
  }
  if ($entry->metaDescription) {
    $object['metaDescription'] = $entry->metaDescription;
  }
  if ($entry->slug) {
    $object['slug'] = $entry->slug;
  }
  if ($entry->subtitle) {
    $object['subtitle'] = $entry->subtitle;
  }
  if ($entry->highlight) {
    $object['highlight'] = $entry->highlight;
  }

  if ($photos) {
    $object['image'] = $photos;
  }
  if ($tags) {
    $object['tags'] = $tags;
  }
  if ($blocks) {
    $object['blocks'] = $blocks;
  }
  if ($entry->slug) {
    $object['jsonUrl'] = UrlHelper::url("api/{$handle}/{$entry->slug}.json");
  }
  if ($entry->description) {
    $object['description'] = $entry->description;
  }
  if ($entry->descriptionExtra) {
    $object['descriptionExtra'] = $entry->descriptionExtra;
  }

  if ($entry->colorBackground) {
    $object['color'] = $entry->colorBackground;
  }
  if ($entry->director) {
    $object['director'] = $entry->director[0]->title;
  }
  if ($entry->facebookLink) {
    $object['facebookLink'] = $entry->facebookLink;
  }
  if ($entry->instagramLink) {
    $object['instagramLink'] = $entry->instagramLink;
  }
  if ($entry->behanceLink) {
    $object['behanceLink'] = $entry->behanceLink;
  }
  if ($entry->linkedinLink) {
    $object['linkedinLink'] = $entry->linkedinLink;
  }
  if ($entry->vimeoLink) {
    $object['vimeoLink'] = $entry->vimeoLink;
  }
  if ($entry->tables) {
    $object['tables'] = $entry->tables;
  }

  return $object;
}

function getPhotos($entry, $handle) {

  if (!isset($handle)) {
    $handle = $entry->getSection()->handle;
  }

  if (isset($entry->image)) {
    $photos = [];
    foreach ($entry->image as $photo) {
      $photoObj = new stdClass();

      $photoObj->handle = $handle;
      $photoObj->mobile = $photo->getUrl($handle . '_mobile');
      $photoObj->tablet = $photo->getUrl($handle . '_tablet');
      $photoObj->desktop = $photo->getUrl($handle . '_desktop');
      $photoObj->desktop_big = $photo->getUrl($handle . '_desktop_big');
      $photoObj->desktop_extra_big = $photo->getUrl($handle . '_desktop_extra_big');

      $photos[] = $photoObj;
    }

    return $photos;
  }
  else {
    return;
  }
}

function getTags($entry) {
  if (isset($entry->tags)) {
    $tags = [];

    foreach ($entry->tags as $tag) {
      $tagItem = new stdClass();
      $tagItem->title = $tag->title;
      $tagItem->slug = $tag->slug;
      $tags [] = $tagItem;
    }

    return $tags;
  }
  else {
    return;
  }
}

function getImages($element, $handle) {
  //$handle = $element->getSection()->handle;
  $photos = [];

  foreach ($element as $photo) {
    $photoObj = new stdClass();

    $photoObj->handle = $handle;
    $photoObj->mobile = $photo->getUrl($handle . '_mobile');
    $photoObj->tablet = $photo->getUrl($handle . '_tablet');
    $photoObj->desktop = $photo->getUrl($handle . '_desktop');
    $photoObj->desktop_big = $photo->getUrl($handle . '_desktop_big');
    $photoObj->desktop_extra_big = $photo->getUrl($handle . '_desktop_extra_big');

    $photos[] = $photoObj;

  }
  return $photos;
}

function getDirector($entry) {
  if (isset($entry->director[0])) {
    return $entry->director[0]['title'];
  } else {
    return;
  }
}

function getElements($entry, $type) {
  $elementsArray = [];

  foreach ($entry as $element) {
    $handle = $element->getSection()->handle;
    $photos = getPhotos($element,  $type);
    $blocks = getBlocks($element, $handle);
    $tags = getTags($element);
    $director = getDirector($element);

    $elementItem = new stdClass();

    foreach ($element as $key => $el) {
      if ($key == 'id') {
        $elementItem->id = $el;
      }
      if ($key == 'title') {
        $elementItem->title = $el;
      }
      if ($key == 'headline') {
        $elementItem->headline = $el;
      }
      if ($key == 'slug') {
        $elementItem->slug = $el;
      }
      if ($key == 'size') {
        $elementItem->size = $el;
      }
      if ($key == 'subtitle') {
        $elementItem->subtitle = $el;
      }
      $elementItem->handle = $handle;
      $elementItem->url = $element->url;

      if ($key == 'image') {
        $elementItem->image = $photos;
      }

      $elementItem->jsonUrl = UrlHelper::url("api/{$handle}/{$element->id}.json");

      if ($key == 'description') {
        $elementItem->description = $el;
      }
      if ($key == 'blocks') {
        $elementItem->blocks = $blocks;
      }
      if ($key == 'tags') {
        $elementItem->tags = $tags;
      }
      if ($key == 'director') {
        $elementItem->director = getDirector($element);
      }

    }

    $elementsArray[] = $elementItem;
  }

  return $elementsArray;
}

function getBlocks($entry) {

  if (isset($entry->blocks)) {

    $blocks = [];

    foreach ($entry->blocks as $block) {
      $blockItem = new stdClass();
      $type = [];
      foreach ($block->type as $key => $element) {
        if ($key == 'handle') {
          $type[] = $element;
          $blockItem->type = $type[0];
        }
      }

      foreach ($block as $key => $element) {
        if ($key == 'description') {
          $blockItem->description = $element;
        }

        if ($key == 'mainTitle') {
          $blockItem->mainTitle = $element;
        }

        if ($key == 'handle') {
          $blockItem->handle = $element;
        }

        if ($key == 'subtitle') {
          $blockItem->subtitle = $element;
        }
        if ($key == 'size') {
          $blockItem->size = $element;
        }
        if ($key == 'tables') {
          $blockItem->tables = $element;
        }

        if ($key == 'image') {
          $blockItem->image = getImages($element, $blockItem->type . ((isset($blockItem->handle)) ?  '_' . $blockItem->handle : ''));
        }

        if ($key == 'typeElement') {
          $blockItem->typeElement = getElements($element, $type[0].((isset($block->size)) ? '_'.$block->size : ''));
        }

        if ($key == 'items') {
          $blockItem->typeElement = getElements($element, $type[0].((isset($block->size)) ? '_'.$block->size : ''));
        }

      }

      $blocks[] = $blockItem;
    }

    return $blocks;

  } else {
    return;
  }
}