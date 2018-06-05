<?php

namespace Logistics\Jobs\Tenant;

use Illuminate\Bus\Queueable;
use Logistics\DB\Tenant\Tenant;
use Intervention\Image\Facades\Image;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessLogo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tenant;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->tenant->logo) {
            $imageContents = Storage::disk('public')->get($this->tenant->logo);
            
            list($width) = getimagesizefromstring($imageContents);
            
            if ($width >= 600) {
                $image = Image::make($imageContents)
                ->resize(600, null, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->limitColors(255)
                ->encode();
                
                Storage::disk('public')->put($this->tenant->logo, (string)$image);
            }
        }
    }
}
