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
    'api/globals.json' => function() {
      return [
          /* 'elementType' => 'GlobalSet',
          'criteria' => ['handle' => 'footer'],
          'transformer' => function(GlobalSetModel $siteSettings) {
            return [
                'defaultTitle' => $siteSettings->description
            ]; */

            'elementType' =>  'craft\elements\GlobalSet',
            'criteria' => ['handle' => 'footer'],
            'transformer' => function($footer) 
            {
              return [
                'footerText' => $footer->footerText,
                'social' => [
                  'behanceLink' => $footer->behanceLink,
                  'facebookLink' => $footer->facebookLink,
                  'linkedinLink' => $footer->linkedinLink,
                  'instagramLink' => $footer->instagramLink,
                  'vimeoLink' => $footer->vimeoLink,
                ],
                'colorBackground' => $footer->colorBackground,
              ];
                // our data here
            }
        
      ];
  },
    //channels
    'api/<slug:{slug}>.json' => function ($slug) {

      Craft::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Origin', '*');
      Craft::$app->getResponse()->getHeaders()->set('Cache-control', 'max-age=3600');
      Craft::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Headers', 'Cache-control');
      return [
        'elementType' => Entry::class,
        'criteria' => ['section' => $slug],
        'transformer' => function (Entry $entry) {
          $object = getItem($entry, null);
          return $object;
        },
      ];
    },
    'api/<slug:{slug}>/<entryId:\d+>.json' => function ($slug, $entryId) {

      Craft::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Origin', '*');
      Craft::$app->getResponse()->getHeaders()->set('Cache-control', 'max-age=3600');
      Craft::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Headers', 'Cache-control');
      return [
        'elementType' => Entry::class,
        'criteria' => ['section' => $slug, 'id' => $entryId],
        'one' => TRUE,
        'transformer' => function (Entry $entry) {

          if ($entry->getSection()->handle == 'directors') {
            $directors = Craft::$app->getEntries()->getEntryById(54);

            $directorsBlocks = getBlocks($directors);
            $countDirectors = count($directorsBlocks[0]->typeElement);
            $count = 0;

            foreach ($directorsBlocks[0]->typeElement as $directorsBlock) {
              $count++;
              if ($directorsBlock->id == $entry->id) {
                break;
              }
            }

            if ($count >= $countDirectors) {
              $count = 0;
            }

            $nextEntry = array(
              "id" => $directorsBlocks[0]->typeElement[$count]->id,
              "title" => $directorsBlocks[0]->typeElement[$count]->title,
              "slug" => $directorsBlocks[0]->typeElement[$count]->slug,
            );
          } elseif ($entry->getSection()->handle == 'projects') {

            $directorId = $entry->director[0]->id;
            $directorEntry = Craft::$app->getEntries()->getEntryById($directorId);
            $directorProjects = getBlocks($directorEntry);
            $directoProjectsBlocks = $directorProjects[0]->typeElement;

            $countProjects = count($directoProjectsBlocks);
            $count = 0;

            foreach ($directoProjectsBlocks as $directorProject) {
              $count++;
              if ($directorProject->id == $entry->id) {
                break;
              }
            }

            if ($count >= $countProjects) {
              $count = 0;
            }

            $nextEntry = array(
              "id" => $directorProjects[0]->typeElement[$count]->id,
              "title" => $directorProjects[0]->typeElement[$count]->title,
              "slug" => $directorProjects[0]->typeElement[$count]->slug,
            );

          } else {

            $nextEntry = '';
          }

          //$nextEntry = $entry->getNext($criteria);
          $object = getItem($entry, $nextEntry);
          return $object;
        },
      ];
    },

    //pages
    'api/pages/<slug:{slug}>.json' => function ($slug) {

    Craft::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Origin', '*');
    Craft::$app->getResponse()->getHeaders()->set('Cache-control', 'max-age=3600');
    Craft::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Headers', 'Cache-control');
      //    HeaderHelper::setHeader([
      //      'Access-Control-Allow-Origin' => 'http://test.thebrut.es'
      // ]);

      return [
        'elementType' => Entry::class,
        'pageParam' => 'pg',
        'criteria' => ['section' => $slug],
        'transformer' => function (Entry $entry) {
          $object = getItem($entry, null);
          return $object;
        },
      ];
    },
  ]
];

function getItem ($entry, $nextEntry) {
  $highlight= $entry->highlight;
  $handle = $entry->getSection()->handle;
  $photos = getPhotos($entry, $handle);
//  $photosSquare = getPhotos($entry, $handle . '_square');
  $blocks = getBlocks($entry);
  $tags = $nextEntry == null ? '' : getTags($entry);

  if ($entry->id) {
    $object['id'] = $entry->id;
  }
  if ($handle) {
    $object['handle'] = $handle;

    if ($nextEntry != '') {
      $object['nextEntry'] = $nextEntry;
    }
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

  if ($entry->imageSquare != '') {
    $photosSquare = getPhotosSquare($entry, $handle . '_square');
    $object['imageSquare'] = $photosSquare;
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
  if ($entry->followText) {
    $object['followText'] = $entry->followText;
  }
  if ($entry->vimeoId) {
    $object['vimeoId'] = $entry->vimeoId;
  }
  if ($entry->vimeoUrl) {
    $object['vimeoUrl'] = $entry->vimeoUrl;
  }
  if ($entry->colorBackground) {
    $object['color'] = $entry->colorBackground;
  }
  if (isset($entry->director[0])) {
    $object['director'] = $entry->director[0];
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

  if ($entry->videoMp4[0] != 'default') {
    $object['videoMp4'] = $entry->videoMp4[0];
  }
  if ($entry->videoWebm[0] != 'default') {
    $object['videoWebm'] = $entry->videoWebm[0];
  }

  return $object;
}

function getPhotosCount ($entry, $handle, $count) {

  if (!isset($handle)) {
    $handle = $entry->getSection()->handle;
  }

  if (isset($entry->image)) {
    $photos = [];
    foreach ($entry->image as $photo) {
      $photoObj = new stdClass();

      $photoObj->handle = $handle;
      $photoObj->mobile = $photo->getUrl($handle . $count . '_mobile');
      $photoObj->tablet = $photo->getUrl($handle . $count . '_tablet');
      $photoObj->desktop = $photo->getUrl($handle . $count . '_desktop');
      $photoObj->desktop_big = $photo->getUrl($handle . $count . '_desktop_big');
      $photoObj->desktop_extra_big = $photo->getUrl($handle . $count . '_desktop_extra_big');

      $photos[] = $photoObj;
    }

    return $photos;
  }
  else {
    return;
  }
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

function getPhotosSquare($entry, $handle) {

  if (!isset($handle)) {
    $handle = $entry->getSection()->handle;
  }

  if (isset($entry->imageSquare)) {
    $photos = [];
    foreach ($entry->imageSquare as $photo) {
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
  $count = 0;

  foreach ($entry as $element) {
    $handle = $element->getSection()->handle;
    
    $blocks = getBlocks($element, $handle);
    $tags = getTags($element);
    $director = getDirector($element);
    $elementItem = new stdClass();
    
    if ($type == 'blockDoubleItems') {
      $photos = getPhotosCount($element, $type, $count);
    } else {
      $photos = getPhotos($element, $type);
    }
    $count ++;

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
      if ($key == 'videoMp4') {
        if ($el[0] != 'default') {
          $elementItem->videoMp4 = $el[0];
        }
      }
      if ($key == 'videoWebm') {
        if ($el[0] != 'default') {
          $elementItem->videoWebm = $el[0];
        }
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

        if ($key == 'vimeoId') {
          $blockItem->vimeoId = $element;
        }

        if ($key == 'vimeoUrl') {
          $blockItem->vimeoUrl = $element;
        }

        if ($key == 'image') {
          $blockItem->image = getImages($element, $blockItem->type . ((isset($blockItem->handle)) ?  '_' . $blockItem->handle . '' : '') );
        }

        if ($key == 'typeElement') {
          $blockItem->typeElement = getElements($element, $type[0].((isset($block->size)) ? '_'.$block->size : ''));
        }

        if ($key == 'director') {
          $blockItem->director = $element->director;
        }

        if ($key == 'items') {
          $blockItem->typeElement = getElements($element, $type[0].((isset($block->size)) ? '_'.$block->size : ''));
        }
        
        if ($key == 'itemsProjects') {
         
          foreach ($element as $key => $item) {
            $itemProject = new stdClass();
            $project = new stdClass();
            $project->id = $item->item[0]->id;

            $project->handle = $item->item[0]->getSection()->handle;
            $project->title = $item->item[0]->title;
            $project->slug = $item->item[0]->slug;
            $project->image = getImages($item->item[0]->image, $blockItem->type. $item->position);
            $project->headline = $item->item[0]->headline;
            $project->uri = $item->item[0]->uri;
            $project->status = $item->item[0]->status;
            $project->vimeoId = $item->item[0]->vimeoId;
            $project->vimeoUrl = $item->item[0]->vimeoUrl;
            $project->tags = [];

            foreach ($item->item[0]->tags as $key => $tag) {
               $tagItem = new stdClass();
                $tagItem->title = $tag->title;
                $tagItem->slug = $tag->slug;
                $project->tags[] = $tagItem;
            }

            $itemProject->item = $project;
            $itemProject->position = $item->position;

            $blockItem->itemsProjects[] = $itemProject;
          }
        }

        if ($key == 'imagesList') {

          $blockItem->images = [];

          foreach ($element as $key => $item) {
            $image = new stdClass();

            $image->position = $item->position;
            $image->type = $blockItem->type;
            if ($item->image[0]) {
              $image->item = getImages($item->image, 'blockListProjects' . $image->position);
            }
            
            $blockItem->images[] = $image;
          }
        }
      }

      $blocks[] = $blockItem;
    }

    return $blocks;

  } else {
    return;
  }
}
