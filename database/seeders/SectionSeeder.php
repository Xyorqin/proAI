<?php

namespace Database\Seeders;

use App\Models\Structure\Section;
use App\Models\Structure\Subsection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            '📌 Holat tahlili' => [
                'Bozor tahlili',
                'Raqobatchi tahlili',
                'Iste’molchi tahlili',
                'Mahsulot/xizmat tahlili',
            ],
            '🎯 O’sish nuqtalarini aniqlash' => [
                'Aylanmani hisoblash',
                'Bozorni o‘lchash (TAM/SAM/SOM)',
                'Raqobatchilar ro‘yxati',
                'Ma’lumot yig’ish',
                'Ma’lumotlarni tahlil qilish',
                'Ehtiyojlarni aniqlash',
                'CJM – xarid yo‘li',
                'Value Proposition Canvas',
                'FAB tahlil',
                'SWOT tahlil',
                '7P, 4P, 4C, 4E bo‘yicha aniqlash',
            ],
            '🧩 Marketing strategiyasini tuzish' => [
                'Biznes holatini aniqlash (Adizes)',
                'Bozor hajmi',
                'Mahsulot va xizmatlar',
                'Narx siyosati',
                'Narxni taqqoslash',
                'Tarqatish strategiyasi',
                '7P asosida o‘sish',
                'Marketing byudjeti',
                'CJM integratsiyasi',
                'Mahsulot hayot sikli',
                'Risklar va xatarlar',
            ],
            '🗺 Taktik harakatlarni belgilash' => [
                'Eyzenxauer matritsasi',
                'Marketing funksiyalariga taqsimlash',
                'Pareto prinsip (20/80)',
                'CMO uchun to-do list',
            ],
            '🚀 Amalga oshirish rejasi' => [
                'Harakatlar taqvimi',
                'Gantt diagramma',
                'KPI belgilash',
            ],
            '📊 Monitoring va nazorat' => [
                'KPI monitoring',
                'Kanallar samaradorligi',
                'Raqamli analitika',
                'Doimiy takomillashtirish',
            ],
        ];

        foreach ($data as $sectionName => $subsections) {
            $section = Section::updateOrCreate(
                ['slug' => Str::slug(strip_tags($sectionName))],
                ['name' => $sectionName]
            );

            foreach ($subsections as $subName) {
                Subsection::updateOrCreate(
                    [
                        'slug' => Str::slug($section->slug . '-' . strip_tags($subName)),
                        'section_id' => $section->id,
                    ],
                    ['name' => $subName]
                );
            }
        }
    }
}
