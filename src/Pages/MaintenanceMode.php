<?php

namespace Jasmine\MaintenanceMode\Pages;

use Illuminate\Support\Str;
use Jasmine\Jasmine\Bread\Fields\FieldsManifest;
use Jasmine\Jasmine\Bread\Fields\GroupedField;
use Jasmine\Jasmine\Bread\Fields\InputField;
use Jasmine\Jasmine\Bread\Fields\SwitchField;
use Jasmine\Jasmine\Models\JasminePage;

class MaintenanceMode extends JasminePage
{
    public function getLocale() { return null; }
    
    public static function boot()
    {
        parent::boot();
        
        static::saving(function (MaintenanceMode $p) {
            $content = $p->content;
            
            foreach ($content['bypass_tokens'] ?? [] as $k => $token) {
                if (!$content['bypass_tokens'][$k]['name']) {
                    unset($content['bypass_tokens'][$k]);
                    continue;
                }
                
                if ($content['bypass_tokens'][$k]['url']
                    && preg_match('/\?mm-token=([0-9a-f]+)/', $token['url'], $m)) {
                    $content['bypass_tokens'][$k]['url'] = $m[1];
                } else {
                    $content['bypass_tokens'][$k]['url'] =
                        hash_hmac('sha256', Str::random(40), config('app.key'));
                }
            }
            
            $p->content = $content;
        });
        
        static::retrieved(function (MaintenanceMode $p) {
            $content = $p->content;
            
            foreach ($content['bypass_tokens'] ?? [] as $k => $token) {
                $content['bypass_tokens'][$k]['url'] = url('/') . '?mm-token=' . $token['url'];
            }
            
            $p->content = $content;
        });
    }
    
    public static function fieldsManifest(): FieldsManifest
    {
        return new FieldsManifest([
            'col-md-12' => [
                __('Maintenance Mode') => [
                    (new SwitchField('status'))
                        ->setWidth('col-md-2')
                        ->setOptions(['options' => ['Off', 'On']]),
                    
                    (new GroupedField('bypass_tokens'))
                        ->setRepeats(999)->setRepeatsWidth('col-md-3')->setFields([
                            (new InputField('name')),
                            (new InputField('url'))->setOptions(['readonly' => true]),
                        ]),
                ],
            ],
        ]);
    }
}
