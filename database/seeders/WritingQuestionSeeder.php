<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

/**
 * Comprehensive Writing Question Seeder
 * Covers Academic Task 1 (6 chart types), General Task 1 (6 letter types),
 * Academic Task 2 (14 essays), General Task 2 (8 essays)
 */
class WritingQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAcademicTask1();
        $this->seedGeneralTask1();
        $this->seedAcademicTask2();
        $this->seedGeneralTask2();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACADEMIC TASK 1 — 10 prompts covering all major chart/diagram types
    // ─────────────────────────────────────────────────────────────────────────
    protected function seedAcademicTask1(): void
    {
        $questions = [
            // ── Line Graph ──
            [
                'title' => 'Internet Usage by Age Group (2005–2020)',
                'content' => "The line graph below shows the percentage of people in three age groups who used the internet at least once a week in a European country between 2005 and 2020.\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.\n\nWrite at least 150 words.",
                'metadata' => [
                    'chart_type' => 'line',
                    'chart_title' => 'Weekly Internet Usage by Age Group (%) — 2005 to 2020',
                    'y_label' => 'Percentage of users (%)',
                    'labels' => ['2005', '2008', '2011', '2014', '2017', '2020'],
                    'datasets' => [
                        ['label' => '16–34 years', 'data' => [72, 80, 88, 93, 97, 99], 'color' => '#06b6d4'],
                        ['label' => '35–54 years', 'data' => [48, 58, 68, 78, 86, 92], 'color' => '#8b5cf6'],
                        ['label' => '55–74 years', 'data' => [12, 20, 32, 46, 58, 70], 'color' => '#f97316'],
                    ],
                    'key_features' => 'All three groups increased over 15 years. Youngest group (16–34) started highest at 72% and reached near-saturation at 99%. Oldest group (55–74) showed the steepest proportional rise, from 12% to 70%. Middle group rose consistently from 48% to 92%. Gap between youngest and oldest narrowed from 60pp in 2005 to 29pp in 2020.',
                ],
            ],

            // ── Bar Chart ──
            [
                'title' => 'Museum Visitor Numbers by Country (2019)',
                'content' => "The bar chart below shows the number of visitors (in millions) to the five most popular museums in different countries in 2019.\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.\n\nWrite at least 150 words.",
                'metadata' => [
                    'chart_type' => 'bar',
                    'chart_title' => 'Museum Visitor Numbers (millions) — 2019',
                    'y_label' => 'Visitors (millions)',
                    'labels' => ['Louvre (France)', 'Smithsonian (USA)', 'British Museum (UK)', 'Vatican Museums (Italy)', 'National Museum (China)'],
                    'datasets' => [
                        ['label' => 'Visitors (millions)', 'data' => [9.6, 8.4, 6.2, 6.9, 8.0], 'color' => '#06b6d4'],
                    ],
                    'key_features' => 'Louvre leads with 9.6 million. Smithsonian (8.4M) and National Museum of China (8.0M) are close. Vatican Museums (6.9M) outrank British Museum (6.2M). Overall range: 9.6M to 6.2M — a 3.4M spread.',
                ],
            ],

            // ── Pie Chart ──
            [
                'title' => 'Causes of Global Deforestation',
                'content' => "The pie chart below shows the main causes of deforestation globally, based on data from an environmental research organisation.\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.\n\nWrite at least 150 words.",
                'metadata' => [
                    'chart_type' => 'pie',
                    'chart_title' => 'Global Causes of Deforestation (%)',
                    'labels' => ['Cattle ranching', 'Small-scale farming', 'Large-scale agriculture', 'Logging', 'Wildfires', 'Infrastructure'],
                    'datasets' => [
                        ['label' => 'Share', 'data' => [41, 27, 14, 9, 5, 4], 'color' => null],
                    ],
                    'key_features' => 'Cattle ranching is by far the largest cause at 41%. Combined farming activities (small-scale 27% + large-scale 14%) account for 41% — equal to cattle ranching. Logging contributes 9%, wildfires 5%, and infrastructure 4%. Agricultural activities (farming + ranching) together account for 82% of all deforestation.',
                ],
            ],

            // ── Table ──
            [
                'title' => 'Average Monthly Wages in Five Countries (1990 and 2020)',
                'content' => "The table below shows the average monthly wages (in US dollars) in five countries in 1990 and 2020, and the percentage change over the period.\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.\n\nWrite at least 150 words.",
                'metadata' => [
                    'chart_type' => 'table',
                    'chart_title' => 'Average Monthly Wages (USD) — 1990 and 2020',
                    'columns' => ['Country', '1990', '2020', '% Change'],
                    'rows' => [
                        ['Germany', '$1,820', '$3,940', '+116%'],
                        ['Brazil', '$310', '$720', '+132%'],
                        ['India', '$90', '$480', '+433%'],
                        ['China', '$80', '$1,150', '+1,338%'],
                        ['USA', '$2,650', '$5,370', '+103%'],
                    ],
                    'key_features' => 'China experienced by far the largest percentage increase (+1,338%), though it started lowest. India also showed dramatic growth (+433%). USA had the highest absolute wages in both years. Germany and Brazil showed moderate but consistent growth. All five countries more than doubled wages in nominal terms.',
                ],
            ],

            // ── Two-chart comparison ──
            [
                'title' => 'Student Satisfaction with University Services (2015 and 2022)',
                'content' => "The two bar charts below show the percentage of students who rated different university services as 'satisfactory' or above in 2015 and 2022.\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.\n\nWrite at least 150 words.",
                'metadata' => [
                    'chart_type' => 'bar_grouped',
                    'chart_title' => 'Student Satisfaction with University Services (%) — 2015 vs 2022',
                    'y_label' => 'Satisfaction rate (%)',
                    'labels' => ['Library', 'Accommodation', 'IT Services', 'Sports Facilities', 'Career Support'],
                    'datasets' => [
                        ['label' => '2015', 'data' => [82, 55, 61, 74, 48], 'color' => '#8b5cf6'],
                        ['label' => '2022', 'data' => [79, 68, 85, 71, 62], 'color' => '#06b6d4'],
                    ],
                    'key_features' => 'IT Services saw the sharpest rise from 61% to 85%. Accommodation improved substantially (+13pp). Library satisfaction fell slightly (82% to 79%). Sports Facilities dipped marginally. Career Support improved by 14pp — the second largest gain.',
                ],
            ],

            // ── Map ──
            [
                'title' => 'Town Centre Development (2000 and Present Day)',
                'content' => "The two maps below show the layout of a small town centre in 2000 and the present day.\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.\n\nWrite at least 150 words.",
                'metadata' => [
                    'chart_type' => 'map',
                    'chart_title' => 'Town Centre Layout — 2000 and Present Day',
                    'year_a' => '2000',
                    'year_b' => 'Present Day',
                    'changes' => [
                        ['location' => 'North side of Main Street', 'from' => 'Open market', 'to' => 'Shopping mall (3 storeys)'],
                        ['location' => 'West end of town', 'from' => 'Industrial warehouse', 'to' => 'Residential apartment complex'],
                        ['location' => 'Town centre', 'from' => 'Parking lot', 'to' => 'Public park with fountain'],
                        ['location' => 'South side of Main Street', 'from' => 'Row of independent shops', 'to' => 'Unchanged — still independent shops'],
                        ['location' => 'East end', 'from' => 'Empty land', 'to' => 'Business park with 4 office buildings'],
                        ['location' => 'River bank', 'from' => 'Unused', 'to' => 'Pedestrian walkway and café'],
                    ],
                    'key_features' => 'Significant redevelopment: industrial and unused areas converted to residential and commercial use. Town centre replaced parking with a park. South side remained unchanged. Overall: more residential, more commercial, more green space.',
                ],
            ],

            // ── Process diagram ──
            [
                'title' => 'How Solar Panels Generate Electricity',
                'content' => "The diagram below illustrates the process by which solar panels generate electricity for domestic use.\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.\n\nWrite at least 150 words.",
                'metadata' => [
                    'chart_type' => 'process',
                    'chart_title' => 'Solar Panel Electricity Generation Process',
                    'steps' => [
                        ['icon' => '☀️', 'label' => 'Sunlight strikes panels', 'detail' => 'Photovoltaic (PV) cells on roof panels absorb sunlight'],
                        ['icon' => '⚡', 'label' => 'DC electricity produced', 'detail' => 'Electrons are freed and create direct current (DC)'],
                        ['icon' => '🔄', 'label' => 'Inverter conversion', 'detail' => 'Inverter converts DC to alternating current (AC)'],
                        ['icon' => '🏠', 'label' => 'Powers the home', 'detail' => 'AC electricity distributed to household appliances'],
                        ['icon' => '🔋', 'label' => 'Excess stored or exported', 'detail' => 'Surplus stored in battery or fed into the national grid'],
                        ['icon' => '💷', 'label' => 'Grid payment received', 'detail' => 'Homeowner receives payment for exported electricity'],
                    ],
                    'key_features' => 'Six-stage linear process. Key transformation: DC to AC via inverter. Unique feature: two output paths — home use and grid export. Process is cyclic in the sense that stored power can be used at night.',
                ],
            ],

            // ── Line + area: two datasets ──
            [
                'title' => 'CO₂ Emissions and GDP Growth (1990–2020)',
                'content' => "The graph below shows the relationship between CO₂ emissions per capita (tonnes) and GDP per capita (USD thousands) in a particular country between 1990 and 2020.\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.\n\nWrite at least 150 words.",
                'metadata' => [
                    'chart_type' => 'line_dual_axis',
                    'chart_title' => 'CO₂ Emissions vs GDP Per Capita — 1990 to 2020',
                    'labels' => ['1990', '1995', '2000', '2005', '2010', '2015', '2020'],
                    'datasets' => [
                        ['label' => 'CO₂ per capita (tonnes)', 'data' => [8.2, 8.6, 9.1, 9.8, 9.4, 8.7, 7.9], 'axis' => 'left', 'color' => '#f43f5e'],
                        ['label' => 'GDP per capita (USD thousands)', 'data' => [22, 25, 31, 37, 39, 44, 50], 'axis' => 'right', 'color' => '#06b6d4'],
                    ],
                    'key_features' => 'GDP grew continuously from $22k to $50k — a 127% increase. CO₂ peaked in 2005 at 9.8 tonnes then declined to 7.9 tonnes by 2020, suggesting decoupling of economic growth from emissions after 2005. Peak-to-2020 emissions fell by 19% while GDP grew 35% in the same period.',
                ],
            ],

            // ── Bar chart (horizontal) ──
            [
                'title' => 'Time Spent on Leisure Activities per Week (2021)',
                'content' => "The bar chart below shows the average number of hours per week that adults in a particular country spent on different leisure activities in 2021.\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.\n\nWrite at least 150 words.",
                'metadata' => [
                    'chart_type' => 'bar',
                    'chart_title' => 'Average Weekly Leisure Hours by Activity — Adults, 2021',
                    'y_label' => 'Hours per week',
                    'labels' => ['Watching TV/streaming', 'Social media', 'Exercise/sport', 'Reading', 'Socialising in person', 'Gaming', 'Other hobbies'],
                    'datasets' => [
                        ['label' => 'Hours per week', 'data' => [18.5, 12.3, 4.2, 3.8, 6.1, 5.4, 3.2], 'color' => '#06b6d4'],
                    ],
                    'key_features' => 'Television/streaming dominates at 18.5 hours — significantly more than any other activity. Social media is second at 12.3 hours. Together these screen-based activities account for over 56% of all tracked leisure time. Physical/social activities (exercise 4.2h, socialising 6.1h) occupy far less time. Reading and hobbies are the lowest at under 4 hours each.',
                ],
            ],

            // ── Stacked bar (grouped comparison) ──
            [
                'title' => 'University Degrees by Subject and Gender (2022)',
                'content' => "The chart below shows the percentage of male and female graduates in six academic subject areas at a UK university in 2022.\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.\n\nWrite at least 150 words.",
                'metadata' => [
                    'chart_type' => 'bar_grouped',
                    'chart_title' => 'Graduate Distribution by Subject and Gender (%) — UK University, 2022',
                    'y_label' => 'Percentage of graduates (%)',
                    'labels' => ['Computer Science', 'Nursing', 'Law', 'Engineering', 'Education', 'Business'],
                    'datasets' => [
                        ['label' => 'Male', 'data' => [78, 14, 48, 81, 22, 55], 'color' => '#06b6d4'],
                        ['label' => 'Female', 'data' => [22, 86, 52, 19, 78, 45], 'color' => '#f97316'],
                    ],
                    'key_features' => 'Strong gender imbalances in STEM and care fields: CS (78% male), Engineering (81% male), Nursing (86% female), Education (78% female). Law is the most balanced at 48% male / 52% female. Business is moderately male-dominated at 55%.',
                ],
            ],
        ];

        foreach ($questions as $q) {
            Question::updateOrCreate(
                ['title' => $q['title'], 'category' => 'writing_academic_task1'],
                [
                    'type'      => 'writing',
                    'content'   => $q['content'],
                    'metadata'  => json_encode($q['metadata']),
                    'active'    => true,
                    'min_words' => 150,
                ]
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GENERAL TASK 1 — 8 letters covering all tone/purpose types
    // ─────────────────────────────────────────────────────────────────────────
    protected function seedGeneralTask1(): void
    {
        $questions = [
            [
                'title' => 'Request for Information About a Course',
                'content' => "You recently saw an advertisement for an English language course at a local college. You are interested in enrolling.\n\nWrite a letter to the college. In your letter:\n- Explain why you want to take the course\n- Ask for details about the course content and schedule\n- Enquire about fees and any available financial assistance\n\nWrite at least 150 words. You do NOT need to write any addresses.",
                'metadata' => ['tone' => 'formal', 'purpose' => 'information_request'],
            ],
            [
                'title' => 'Complaint About Noisy Neighbours',
                'content' => "You live in an apartment building. Your neighbours have been making excessive noise at night, which is disturbing your sleep.\n\nWrite a letter to the building manager. In your letter:\n- Describe the problem in detail\n- Explain how it is affecting you and other residents\n- Say what action you would like the manager to take\n\nWrite at least 150 words. You do NOT need to write any addresses.",
                'metadata' => ['tone' => 'formal', 'purpose' => 'complaint'],
            ],
            [
                'title' => 'Job Application Letter',
                'content' => "You have seen an advertisement for a job as a customer service representative at an international hotel chain.\n\nWrite a letter of application. In your letter:\n- Explain why you are interested in this particular job\n- Describe your relevant experience and qualifications\n- Say why you believe you would be a suitable candidate\n\nWrite at least 150 words. You do NOT need to write any addresses.",
                'metadata' => ['tone' => 'formal', 'purpose' => 'application'],
            ],
            [
                'title' => 'Invitation to a Friend\'s Event',
                'content' => "You are organising a party to celebrate a personal achievement. You want to invite a close friend who lives in another city.\n\nWrite a letter to your friend. In your letter:\n- Tell your friend about your achievement and why you are celebrating\n- Describe the party and what you have planned\n- Persuade your friend to attend and provide any necessary travel information\n\nWrite at least 150 words. You do NOT need to write any addresses.",
                'metadata' => ['tone' => 'informal', 'purpose' => 'invitation'],
            ],
            [
                'title' => 'Complaint to an Airline',
                'content' => "You recently travelled on a long-distance flight and experienced several problems that ruined your journey.\n\nWrite a letter to the airline's customer services department. In your letter:\n- Describe the problems you experienced during the flight\n- Explain the impact these problems had on you\n- State what compensation or action you expect from the airline\n\nWrite at least 150 words. You do NOT need to write any addresses.",
                'metadata' => ['tone' => 'formal', 'purpose' => 'complaint'],
            ],
            [
                'title' => 'Request for a Reference Letter',
                'content' => "You are applying for a postgraduate scholarship programme and need a reference letter from a previous employer.\n\nWrite a letter to your former manager. In your letter:\n- Remind your manager who you are and when you worked together\n- Explain what the scholarship is for and why you are applying\n- Ask them to write a reference letter and give them the deadline\n\nWrite at least 150 words. You do NOT need to write any addresses.",
                'metadata' => ['tone' => 'semi_formal', 'purpose' => 'request'],
            ],
            [
                'title' => 'Recommendation Letter to a Friend',
                'content' => "A friend of yours is considering moving to the city where you currently live. They have asked for your thoughts on the city.\n\nWrite a letter to your friend. In your letter:\n- Describe the advantages of living in this city\n- Mention any possible difficulties your friend might face\n- Suggest how your friend can prepare for the move\n\nWrite at least 150 words. You do NOT need to write any addresses.",
                'metadata' => ['tone' => 'informal', 'purpose' => 'recommendation'],
            ],
            [
                'title' => 'Complaint About a Defective Product',
                'content' => "You bought a new laptop computer from an online retailer three weeks ago. It has developed a serious fault and you have been unable to resolve the issue through the company's online support.\n\nWrite a letter to the company. In your letter:\n- Describe the product and the fault\n- Explain what steps you have already taken to try to resolve the problem\n- State what action you want the company to take and by when\n\nWrite at least 150 words. You do NOT need to write any addresses.",
                'metadata' => ['tone' => 'formal', 'purpose' => 'complaint'],
            ],
        ];

        foreach ($questions as $q) {
            Question::updateOrCreate(
                ['title' => $q['title'], 'category' => 'writing_general_task1'],
                [
                    'type'      => 'writing',
                    'content'   => $q['content'],
                    'metadata'  => json_encode($q['metadata']),
                    'active'    => true,
                    'min_words' => 150,
                ]
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACADEMIC TASK 2 — 14 essays across all major IELTS themes and question types
    // ─────────────────────────────────────────────────────────────────────────
    protected function seedAcademicTask2(): void
    {
        $questions = [
            // Education
            [
                'title'   => 'Online vs Traditional Education',
                'content' => "In recent years, online education has become increasingly popular. Some people argue that online learning will eventually replace traditional classroom-based education.\n\nTo what extent do you agree or disagree with this view?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'Children Learning Foreign Languages',
                'content' => "Some experts believe that it is better for children to begin learning a foreign language at primary school rather than secondary school.\n\nDo the advantages of introducing foreign languages at an early age outweigh the disadvantages?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'Tuition Fees and University Access',
                'content' => "In many countries, university tuition fees have been rising significantly. Some people argue that higher education should be free for all students, while others believe that students should pay for their own education.\n\nDiscuss both views and give your own opinion.\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],

            // Environment
            [
                'title'   => 'Individual Action vs Government Policy on Climate Change',
                'content' => "Some people think that individuals can do very little to address climate change and that it is mainly the responsibility of governments and large corporations.\n\nTo what extent do you agree or disagree?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'Plastic Pollution Solutions',
                'content' => "The amount of plastic waste in the world's oceans is causing serious environmental problems. Many scientists argue that restricting the production of single-use plastic items is the most effective solution.\n\nDo you agree or disagree with this statement?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'Ecotourism: Benefits and Drawbacks',
                'content' => "Ecotourism is becoming increasingly popular as a way for people to experience natural environments. Proponents argue that it protects fragile ecosystems and supports local communities, while critics claim that any form of tourism is ultimately damaging to the natural environment.\n\nDiscuss both views and give your own opinion.\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],

            // Technology & Society
            [
                'title'   => 'Artificial Intelligence and the Future of Work',
                'content' => "Artificial intelligence and automation are increasingly replacing human workers in many industries. Some people see this as a positive development, while others believe it will cause widespread unemployment and social instability.\n\nDiscuss both views and give your own opinion.\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'Social Media and Young People\'s Mental Health',
                'content' => "Research suggests that heavy use of social media platforms is having a negative impact on the mental health of young people.\n\nWhat do you think are the causes of this problem? What measures can be taken to address it?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],

            // Health
            [
                'title'   => 'Government Responsibility for Public Health',
                'content' => "Some people believe that governments have a responsibility to ensure the health of their citizens by regulating unhealthy foods, taxing sugary drinks, and funding health education programmes. Others argue that individuals should be free to make their own lifestyle choices.\n\nDiscuss both views and give your own opinion.\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'Preventive Healthcare vs Treatment',
                'content' => "It is argued that governments should invest more money in preventive healthcare rather than in treating diseases. \n\nDo you agree or disagree with this view?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],

            // Society & Culture
            [
                'title'   => 'Ageing Populations and Social Challenges',
                'content' => "Many developed countries are experiencing an ageing population, which is creating significant social and economic challenges.\n\nWhat are the main causes of this trend? What are the most serious problems that an ageing population creates, and what measures can be taken to deal with them?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'Cultural Homogenisation and Globalisation',
                'content' => "Some people argue that globalisation is leading to a loss of cultural diversity, as local traditions and languages are being replaced by a dominant global culture.\n\nDo you think this is a positive or negative development?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],

            // Work & Economics
            [
                'title'   => 'Remote Working: Productivity and Community',
                'content' => "The rise of remote working has been celebrated as a positive shift in working life. However, some employers and sociologists argue that it is damaging workplace culture and reducing productivity.\n\nDiscuss both views and give your own opinion.\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'Equal Pay for Men and Women',
                'content' => "Despite legislation in many countries, a significant pay gap between men and women in the same profession still exists.\n\nWhy does this problem persist? What measures could governments and employers take to ensure equal pay?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
        ];

        foreach ($questions as $q) {
            Question::updateOrCreate(
                ['title' => $q['title'], 'category' => 'writing_academic_task2'],
                [
                    'type'      => 'writing',
                    'content'   => $q['content'],
                    'metadata'  => json_encode(['question_type' => $this->classifyTaskType($q['content'])]),
                    'active'    => true,
                    'min_words' => 250,
                ]
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GENERAL TASK 2 — 8 essays
    // ─────────────────────────────────────────────────────────────────────────
    protected function seedGeneralTask2(): void
    {
        $questions = [
            [
                'title'   => 'Benefits of Living Abroad',
                'content' => "An increasing number of people choose to live and work in a foreign country for a period of time.\n\nWhat are the advantages and disadvantages of living and working abroad?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'Advertising and Consumer Behaviour',
                'content' => "Advertising encourages people to buy things they do not need and creates a society where material possessions are overvalued.\n\nTo what extent do you agree or disagree with this view?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'Community Service as Part of Education',
                'content' => "Some people think that young people should be required to do a period of community service — such as working in a hospital or helping elderly people in their neighbourhood — as part of their school curriculum.\n\nDo you agree or disagree with this proposal?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'Public Transport Investment',
                'content' => "Governments should invest more money in improving public transport systems rather than building new roads.\n\nTo what extent do you agree or disagree?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'The Role of Grandparents in Raising Children',
                'content' => "In many countries, grandparents play an important role in raising children while the parents are at work.\n\nDo you think this is a positive or negative trend for the family and for society?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'Crime and Punishment: Prison vs Community Service',
                'content' => "Some people believe that long prison sentences are the most effective way to deter criminals. Others believe that community service and rehabilitation programmes are more effective alternatives.\n\nDiscuss both views and give your own opinion.\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'The Importance of Sleep',
                'content' => "Some research suggests that people in many countries are sleeping less than they did in the past, and this is having a negative impact on health and productivity.\n\nWhat do you think are the causes of this trend? What measures can individuals and governments take to address it?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
            [
                'title'   => 'Zoos: Conservation or Cruelty?',
                'content' => "Some people argue that zoos serve an important role in protecting endangered species and educating the public. Others believe that keeping animals in captivity is cruel and that zoos should be abolished.\n\nDiscuss both views and give your own opinion.\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.\n\nWrite at least 250 words.",
            ],
        ];

        foreach ($questions as $q) {
            Question::updateOrCreate(
                ['title' => $q['title'], 'category' => 'writing_general_task2'],
                [
                    'type'      => 'writing',
                    'content'   => $q['content'],
                    'metadata'  => json_encode(['question_type' => $this->classifyTaskType($q['content'])]),
                    'active'    => true,
                    'min_words' => 250,
                ]
            );
        }
    }

    /**
     * Detect IELTS Task 2 question type from the prompt wording.
     */
    protected function classifyTaskType(string $content): string
    {
        $c = strtolower($content);
        if (str_contains($c, 'discuss both') || str_contains($c, 'discuss both views')) {
            return 'discuss_both_views';
        }
        if (str_contains($c, 'to what extent do you agree or disagree')) {
            return 'opinion';
        }
        if (str_contains($c, 'do you agree or disagree')) {
            return 'opinion';
        }
        if (str_contains($c, 'what are the causes') || str_contains($c, 'what measures')) {
            return 'problem_solution';
        }
        if (str_contains($c, 'positive or negative')) {
            return 'positive_negative';
        }
        if (str_contains($c, 'advantages and disadvantages')) {
            return 'advantages_disadvantages';
        }
        return 'essay';
    }
}
