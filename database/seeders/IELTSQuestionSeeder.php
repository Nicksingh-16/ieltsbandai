<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IELTSQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Cleanup old uncategorized questions (IDs 4-8) as requested
        \App\Models\Question::whereIn('id', [4, 5, 6, 7, 8])->delete();

        // 2. Insert Standardized Speaking Questions
        
        // --- Speaking Part 1 ---
        $speakingPart1 = [
            ['title' => 'Hometown', 'content' => "1. Where is your hometown?\n2. What do you like most about your hometown?\n3. Is there anything you don't like about it?\n4. Do you think you will continue to live there for a long time?"],
            ['title' => 'Work or Study', 'content' => "1. Do you work or are you a student?\n2. What are you studying / What is your job?\n3. Why did you choose that subject/job?\n4. What do you find most interesting about your work/studies?"],
            ['title' => 'Hobbies & Interests', 'content' => "1. What do you like to do in your free time?\n2. Did you have any hobbies when you were a child?\n3. What hobbies are popular in your country?\n4. Is it important for people to have hobbies? Why?"],
            ['title' => 'Technology', 'content' => "1. How often do you use technology?\n2. What is your favorite piece of technology?\n3. Has technology changed your life significantly?\n4. Do you think we rely too much on technology nowadays?"],
            ['title' => 'Transport', 'content' => "1. How do you usually travel to work or school?\n2. What is the public transport like in your city?\n3. Do you prefer travelling by bus or by train? Why?\n4. Will the way people travel change in the future?"],
        ];

        foreach ($speakingPart1 as $q) {
            \App\Models\Question::updateOrCreate(
                ['title' => $q['title'], 'category' => 'speaking_part1'],
                ['type' => 'speaking', 'content' => $q['content'], 'active' => true]
            );
        }

        // --- Speaking Part 2 (Cue Cards) ---
        $speakingPart2 = [
            [
                'title' => 'Describe a memorable journey',
                'content' => "Describe a memorable journey you have been on.\n\nYou should say:\n- Where you went\n- How you travelled\n- Why you went there\n- And explain why the journey was so memorable to you.",
            ],
            [
                'title' => 'Describe an interesting person',
                'content' => "Describe an interesting person you have met recently.\n\nYou should say:\n- Who this person is\n- How you met them\n- What they are like\n- And explain why you find this person interesting.",
            ],
            [
                'title' => 'Describe a piece of electronic equipment',
                'content' => "Describe a piece of electronic equipment that you find useful.\n\nYou should say:\n- What it is\n- How long you have had it\n- What you use it for\n- And explain why you find it so useful.",
            ],
        ];

        foreach ($speakingPart2 as $q) {
            \App\Models\Question::updateOrCreate(
                ['title' => $q['title'], 'category' => 'speaking_part2'],
                ['type' => 'speaking', 'content' => $q['content'], 'active' => true]
            );
        }

        // --- Speaking Part 3 (Follow-up Questions) ---
        $speakingPart3 = [
            [
                'title' => 'Travel and Tourism',
                'content' => "1. Why do you think people like to travel to different places?\n2. What are the advantages and disadvantages of mass tourism?\n3. Do you think it is important for children to travel? Why?\n4. How has the way people travel changed in the last 50 years?",
            ],
            [
                'title' => 'Social Interaction',
                'content' => "1. Is it easier to make friends now than it was in the past? Why?\n2. What qualities do you think are important in a good friend?\n3. How do you think social media has affected human relationships?\n4. Is it better to have a few close friends or many acquaintances?",
            ],
        ];

        foreach ($speakingPart3 as $q) {
            \App\Models\Question::updateOrCreate(
                ['title' => $q['title'], 'category' => 'speaking_part3'],
                ['type' => 'speaking', 'content' => $q['content'], 'active' => true]
            );
        }

        // 3. Insert Standardized Writing Questions
        
        // --- Writing Task 1 Academic (with Metadata) ---
        $writingAcademicTask1 = [
            [
                'title' => 'Population Growth in Urban and Rural Areas',
                'content' => 'The chart below shows the percentage of the population living in urban and rural areas in a particular country between 1950 and 2010.',
                'metadata' => [
                    'chart_type' => 'line',
                    'time_range' => '1950–2010',
                    'units' => 'percentage',
                    'key_trends' => [
                        'Steady increase in urban population from 20% in 1950 to 65% in 2010',
                        'Consistent decline in rural population from 80% to 35% over the same period',
                        'The two lines crossed around 1985 when both were at 50%'
                    ],
                    'major_comparisons' => [
                        'Urban population surpassed rural population in the mid-1980s',
                        'The rate of urban growth was fastest between 1970 and 1990'
                    ],
                    'extremes' => [
                        'Highest urban population: 65% in 2010',
                        'Lowest rural population: 35% in 2010'
                    ],
                    'overview_required' => true
                ]
            ],
            [
                'title' => 'Coffee Production Process',
                'content' => 'The diagram below shows how coffee is produced and prepared for sale.',
                'metadata' => [
                    'chart_type' => 'process',
                    'time_range' => 'n/a',
                    'units' => 'steps',
                    'key_trends' => [
                        '11 distinct stages from bean to supermarket',
                        'Initial stages involve harvesting and drying',
                        'Mid stages involve roasting and grinding',
                        'Final stages involve packaging and delivery'
                    ],
                    'major_comparisons' => [
                        'Complexity of roasting vs simple harvesting',
                        'Transformation from raw bean to consumer product'
                    ],
                    'extremes' => [
                        'First step: Picked by hand',
                        'Last step: Supermarket delivery'
                    ],
                    'overview_required' => true
                ]
            ],
        ];

        foreach ($writingAcademicTask1 as $q) {
            \App\Models\Question::updateOrCreate(
                ['title' => $q['title'], 'category' => 'writing_academic_task1'],
                [
                    'type' => 'writing', 
                    'content' => $q['content'], 
                    'metadata' => json_encode($q['metadata']), 
                    'active' => true,
                    'min_words' => 150
                ]
            );
        }

        // --- Writing Task 1 General (Letters) ---
        $writingGeneralTask1 = [
            [
                'title' => 'Complaint Letter to Landlord',
                'content' => "You are having some trouble with the heating system in your rented apartment. Write a letter to your landlord.\n\nIn your letter:\n- Explain the problem with the heating\n- Describe how this is affecting you\n- Say what you want the landlord to do about it",
                'metadata' => ['tone' => 'formal', 'type' => 'complaint']
            ],
            [
                'title' => 'Apology to a Friend',
                'content' => "You were supposed to meet a friend last week but you could not go. You did not call to tell them.\n\nWrite a letter to your friend. In your letter:\n- Apologize for missing the meeting\n- Explain why you could not go and why you did not call\n- Suggest a time and place to meet again",
                'metadata' => ['tone' => 'informal', 'type' => 'apology']
            ],
        ];

        foreach ($writingGeneralTask1 as $q) {
            \App\Models\Question::updateOrCreate(
                ['title' => $q['title'], 'category' => 'writing_general_task1'],
                [
                    'type' => 'writing', 
                    'content' => $q['content'], 
                    'metadata' => json_encode($q['metadata']), 
                    'active' => true,
                    'min_words' => 150
                ]
            );
        }

        // --- Writing Task 2 (Essays) ---
        $writingTask2 = [
            [
                'title' => 'Technology and Social Interaction',
                'content' => "Some people think that the increasing use of technology in everyday life is making us less social. Others, however, disagree.\n\nDiscuss both these views and give your own opinion.",
                'category' => 'writing_academic_task2'
            ],
            [
                'title' => 'Work-Life Balance',
                'content' => "In many countries, people are working longer hours than ever before. This has a negative effect on their health and family life.\n\nWhat are the causes of this trend? What measures can be taken to solve this problem?",
                'category' => 'writing_general_task2'
            ],
            [
                'title' => 'Education and Success',
                'content' => "Some people believe that a university education is the best way to guarantee a good job. Others think that work experience is more important.\n\nTo what extent do you agree or disagree?",
                'category' => 'writing_academic_task2'
            ],
        ];

        foreach ($writingTask2 as $q) {
            \App\Models\Question::updateOrCreate(
                ['title' => $q['title'], 'category' => $q['category']],
                [
                    'type' => 'writing', 
                    'content' => $q['content'], 
                    'active' => true,
                    'min_words' => 250
                ]
            );
        }
    }
}
