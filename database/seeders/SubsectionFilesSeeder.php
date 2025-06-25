<?php

namespace Database\Seeders;

use App\Models\Structure\Subsection;
use App\Models\Structure\SubsectionFile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubsectionFilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Subsection::all() as $subsection) {
            SubsectionFile::create([
                'subsection_id' => $subsection->id,
                'file_path' => 'files/subsections/sample.txt',
                'content' => 'Sample content for subsection ' . $subsection->id,
                'type' => 'text',
            ]);
            SubsectionFile::create([
                'subsection_id' => $subsection->id,
                'file_path' => 'files/subsections/sample.pdf',
                'content' => 'Sample PDF content for subsection ' . $subsection->id,
                'type' => 'pdf',
            ]);
            SubsectionFile::create([
                'subsection_id' => $subsection->id,
                'file_path' => 'files/subsections/sample.mp4',
                'content' => 'Sample video content for subsection ' . $subsection->id,
                'type' => 'video',
            ]);
        }
    }
}
