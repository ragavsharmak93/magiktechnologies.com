<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OpenAiModelTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('open_ai_models')->delete();

        \DB::table('open_ai_models')->insert(array(
            array('id' => '1', 'name' => 'Ada (The Fastest but Simplest)', 'key' => 'text-ada-001',  'order' => 0, 'created_at' => NULL, 'updated_at' => NULL, 'deleted_at' => '2023-09-04 17:50:22'),
            array('id' => '2', 'name' => 'Babbage (Average)', 'key' => 'text-babbage-001',  'order' => 0, 'created_at' => NULL, 'updated_at' => NULL, 'deleted_at' => '2023-09-04 17:50:22'),
            array('id' => '3', 'name' => 'Curie (Good)', 'key' => 'text-curie-001',  'order' => 0, 'created_at' => NULL, 'updated_at' => NULL, 'deleted_at' => '2023-09-04 17:50:22'),
            array('id' => '4', 'name' => 'Davinci (Powerful but Most Expensive)', 'key' => 'text-davinci-001',  'order' => 0, 'created_at' => NULL, 'updated_at' => NULL, 'deleted_at' => '2023-09-04 17:50:22'),
            array('id' => '5', 'name' => 'ChatGPT 3.5', 'key' => 'gpt-3.5-turbo',  'order' => 0, 'created_at' => NULL, 'updated_at' => NULL, 'deleted_at' => NULL),
            array('id' => '6', 'name' => 'ChatGPT 4 (Beta)', 'key' => 'gpt-4',  'order' => 1, 'created_at' => NULL, 'updated_at' => NULL, 'deleted_at' => NULL),
            array('id' => '7', 'name' => 'ChatGPT 3.5 Turbo-16k', 'key' => 'gpt-3.5-turbo-16k',  'order' => 0,'created_at' => NULL, 'updated_at' => NULL, 'deleted_at' => NULL), 
            array('id' => '8', 'name' => 'ChatGPT 4 Gpt-4-32k', 'key' => 'gpt-4-0613', 'order' => 1, 'created_at' => NULL, 'updated_at' => NULL, 'deleted_at' => NULL), 
            array('id' => '9', 'name' => 'GPT-4 Turbo', 'key' => 'gpt-4-1106-preview',  'order' => 0,'created_at' => NULL, 'updated_at' => NULL, 'deleted_at' => NULL), 
            array('id' => '10', 'name' => 'GPT-4 Turbo with vision', 'key' => 'gpt-4-vision-preview', 'order' => 1, 'created_at' => NULL, 'updated_at' => NULL, 'deleted_at' => NULL), 
            array('id' => '11', 'name' => 'Updated GPT 3.5 Turbo', 'key' => 'gpt-3.5-turbo-1106', 'order' => 1, 'created_at' => NULL, 'updated_at' => NULL, 'deleted_at' => NULL)
            
        ));
    }
}
