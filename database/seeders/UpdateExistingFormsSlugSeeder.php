<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormManage;
use Illuminate\Support\Str;

class UpdateExistingFormsSlugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Update existing forms with slug based on form_route
        $forms = FormManage::whereNull('slug')->orWhere('slug', '')->get();
        
        foreach ($forms as $form) {
            if ($form->form_route) {
                // Use form_route as slug if it exists
                $slug = Str::slug($form->form_route);
                
                // Check if slug already exists
                $count = 1;
                $originalSlug = $slug;
                while (FormManage::where('slug', $slug)->where('id', '!=', $form->id)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
                
                $form->slug = $slug;
                $form->save();
                
                $this->command->info("Updated form '{$form->form_name}' with slug: {$slug}");
            } else {
                // Generate slug from form_name if form_route is empty
                $slug = Str::slug($form->form_name);
                
                // Check if slug already exists
                $count = 1;
                $originalSlug = $slug;
                while (FormManage::where('slug', $slug)->where('id', '!=', $form->id)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
                
                $form->slug = $slug;
                $form->save();
                
                $this->command->info("Updated form '{$form->form_name}' with slug: {$slug}");
            }
        }
        
        $this->command->info('All forms have been updated with slugs!');
    }
}
