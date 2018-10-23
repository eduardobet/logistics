<?php

namespace Logistics\Jobs\Tenant;

use Illuminate\Bus\Queueable;
use Logistics\DB\Tenant\Branch;
use Intervention\Image\Facades\Image;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessBranchLogo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $branch;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Branch $branch)
    {
        $this->branch = $branch;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->branch->logo) {
            $imageContents = Storage::disk('public')->get($this->branch->logo);
            
            list($width) = getimagesizefromstring($imageContents);
            
            if ($width >= 600) {
                $image = Image::make($imageContents)
                ->resize(600, null, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->limitColors(255)
                ->encode();
                
                Storage::disk('public')->put($this->branch->logo, (string)$image);
            }
        }
    }
}
