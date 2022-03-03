<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    protected function insertQuestion($question, $categoryId)
    {
        $data = [
            'category_id' => $categoryId,
            'title' => $question,
            'image' => 'users/7a9oI8L4PHED6zlAS1pbnwhDPZbTC0PYOGPD4YgN.jpeg',
        ];
	$question = Question::create($data);

        return $question->id;
    }

    protected function insertAnswer($data, $questionId, $correct)
    {
        $data = [
            'question_id' => $questionId,
            'title' => $data,
            'is_correct' => $correct
        ];

	Answer::create($data);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file = \Storage::disk('local')->get('public/table2.xlsx');
        $data = \SimpleXLSX::parseData($file);
        $categoryId = 14;

        foreach ($data->rows() as $index => $sheet) {
            $questionId = $this->insertQuestion($sheet[0], $categoryId);
            $this->insertAnswer($sheet[1],  $questionId, true);
            $this->insertAnswer($sheet[2],  $questionId, false);
            $this->insertAnswer($sheet[3],  $questionId, false);
            $this->insertAnswer($sheet[4],  $questionId, false);
        }
    }
}
