<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

/**
 * Comprehensive Speaking Question Seeder
 * Part 1: 18 topic sets (personal/familiar topics)
 * Part 2: 15 cue cards with bullet points
 * Part 3: 15 paired abstract discussion sets
 */
class SpeakingQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPart1();
        $this->seedPart2();
        $this->seedPart3();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PART 1 — Familiar topics (4–5 questions each, 2 minutes total)
    // ─────────────────────────────────────────────────────────────────────────
    protected function seedPart1(): void
    {
        $topics = [
            [
                'title'   => 'Food & Cooking',
                'content' => "1. Do you enjoy cooking? Why or why not?\n2. What is your favourite food and why?\n3. How often do you eat out at restaurants?\n4. Has your diet changed since you were a child?\n5. Do you think people in your country eat healthily?",
            ],
            [
                'title'   => 'Music',
                'content' => "1. What kind of music do you enjoy listening to?\n2. Did you learn to play any musical instruments when you were young?\n3. Do you prefer listening to music alone or with other people?\n4. Has the kind of music you like changed over the years?\n5. Is music important in your culture?",
            ],
            [
                'title'   => 'Reading',
                'content' => "1. Do you enjoy reading books? Why or why not?\n2. What kinds of books do you prefer — fiction or non-fiction?\n3. Did you read a lot when you were a child?\n4. Has the internet changed the way people read?\n5. Do you think it is important for children to develop a reading habit?",
            ],
            [
                'title'   => 'Sports & Exercise',
                'content' => "1. Do you do any sports or exercise regularly?\n2. What sports are most popular in your country?\n3. Did you participate in any sports when you were at school?\n4. Is exercise important for mental health as well as physical health?\n5. Would you prefer to exercise alone or as part of a team?",
            ],
            [
                'title'   => 'Shopping',
                'content' => "1. Do you enjoy shopping? Why or why not?\n2. Do you prefer shopping online or in physical stores?\n3. How has shopping changed in your country in recent years?\n4. Do you ever buy things that you do not really need?\n5. Is shopping more of a social activity or a chore for you?",
            ],
            [
                'title'   => 'Weather & Seasons',
                'content' => "1. What is the weather like where you live?\n2. What is your favourite season and why?\n3. Does weather affect your mood?\n4. Has the climate in your area changed noticeably in recent years?\n5. What kind of weather do you find it hardest to deal with?",
            ],
            [
                'title'   => 'Daily Routine',
                'content' => "1. What time do you usually wake up in the morning?\n2. How do you usually spend your evenings?\n3. Have your daily routines changed significantly in the past few years?\n4. Are you a morning person or an evening person?\n5. Do you have a regular exercise routine?",
            ],
            [
                'title'   => 'Sleep',
                'content' => "1. How many hours of sleep do you usually get each night?\n2. Do you think you get enough sleep? Why or why not?\n3. What do you do if you cannot sleep?\n4. Do you take naps during the day?\n5. Do you think modern life is affecting people's sleep habits?",
            ],
            [
                'title'   => 'Neighbours & Local Community',
                'content' => "1. Do you know your neighbours well?\n2. How important is it to have a good relationship with your neighbours?\n3. Is your local area a good place to live? Why?\n4. Do you participate in any community activities or events?\n5. How have neighbourhoods changed compared to when you were a child?",
            ],
            [
                'title'   => 'Photography',
                'content' => "1. Do you like taking photographs?\n2. What do you most often take photos of?\n3. Do you prefer taking photos or being photographed?\n4. How has digital technology changed photography?\n5. Are photographs an important way to preserve memories for you?",
            ],
            [
                'title'   => 'Celebrations & Festivals',
                'content' => "1. What is the most important festival in your country?\n2. How do you usually celebrate your birthday?\n3. Do you enjoy large gatherings and celebrations, or do you prefer quiet occasions?\n4. Has the way people celebrate festivals changed in your country?\n5. Do you think it is important to preserve traditional celebrations?",
            ],
            [
                'title'   => 'Travel',
                'content' => "1. Do you enjoy travelling? Why or why not?\n2. What is the best place you have ever visited?\n3. Do you prefer travelling to domestic or international destinations?\n4. How do you usually prepare for a trip?\n5. What do you think is the most important thing to consider when planning a holiday?",
            ],
            [
                'title'   => 'The Internet',
                'content' => "1. How many hours a day do you spend on the internet?\n2. What do you mainly use the internet for?\n3. Do you think the internet has more positive or negative effects on society?\n4. Did you use the internet a lot when you were a child?\n5. Can you imagine your life without the internet?",
            ],
            [
                'title'   => 'Animals & Pets',
                'content' => "1. Do you have any pets or have you ever owned one?\n2. What animals are popular as pets in your country?\n3. Do you think it is important for children to grow up with animals?\n4. What do you think about keeping wild animals as pets?\n5. How important are animals to your country's culture or economy?",
            ],
            [
                'title'   => 'Clothes & Fashion',
                'content' => "1. How important is fashion to you personally?\n2. How do you decide what to wear each day?\n3. Do you prefer buying new clothes or wearing the same ones for a long time?\n4. Has the way people dress in your country changed in recent years?\n5. Do you think clothes can reflect someone's personality?",
            ],
            [
                'title'   => 'Time Management',
                'content' => "1. Do you think you manage your time well?\n2. What activities do you spend most of your time on each day?\n3. Do you often feel that you don't have enough time to do everything you need?\n4. How do you deal with a very busy schedule?\n5. Is being punctual important in your culture?",
            ],
            [
                'title'   => 'Art & Creativity',
                'content' => "1. Are you interested in art?\n2. Did you do any art or creative activities at school?\n3. Do you think everyone has some form of creativity?\n4. How important is art for society?\n5. Have you visited any art galleries or museums recently?",
            ],
            [
                'title'   => 'Memory & Learning',
                'content' => "1. Are you good at remembering things?\n2. What kinds of things do you find easy or difficult to memorise?\n3. How do you study or learn new information most effectively?\n4. Have you ever forgotten something really important?\n5. Do you think your memory has changed as you have got older?",
            ],
        ];

        foreach ($topics as $q) {
            Question::updateOrCreate(
                ['title' => $q['title'], 'category' => 'speaking_part1'],
                ['type' => 'speaking', 'content' => $q['content'], 'active' => true]
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PART 2 — Long turn cue cards (1-min prep, 1–2 min response)
    // ─────────────────────────────────────────────────────────────────────────
    protected function seedPart2(): void
    {
        $cards = [
            [
                'title'   => 'Describe a historical building you have visited',
                'content' => "Describe a historical building you have visited.\n\nYou should say:\n- What the building is and where it is located\n- When and why you visited it\n- What you found most interesting or impressive about it\n- And explain why you think it is important to preserve historical buildings.",
            ],
            [
                'title'   => 'Describe a skill you would like to learn',
                'content' => "Describe a skill you would like to learn in the future.\n\nYou should say:\n- What the skill is\n- Why you want to learn it\n- How you would go about learning it\n- And explain how having this skill would benefit your life.",
            ],
            [
                'title'   => 'Describe a time you helped someone',
                'content' => "Describe a time when you helped someone in need.\n\nYou should say:\n- Who the person was and what their situation was\n- How you helped them\n- What challenges you faced in helping them\n- And explain how this experience affected you.",
            ],
            [
                'title'   => 'Describe a book that had an impact on you',
                'content' => "Describe a book that had a significant impact on you.\n\nYou should say:\n- What the book is and who wrote it\n- When you read it and why you chose it\n- What the book is about\n- And explain in what way it influenced or changed you.",
            ],
            [
                'title'   => 'Describe a person you admire',
                'content' => "Describe a person — not a family member — whom you greatly admire.\n\nYou should say:\n- Who this person is\n- How you first learned about them\n- What this person has achieved\n- And explain why you admire them so much.",
            ],
            [
                'title'   => 'Describe a time you had to wait for something',
                'content' => "Describe a time when you had to wait a long time for something.\n\nYou should say:\n- What you were waiting for\n- How long you had to wait\n- What you did while you were waiting\n- And explain how you felt about the waiting experience.",
            ],
            [
                'title'   => 'Describe a country you would like to visit',
                'content' => "Describe a country you have never been to but would like to visit.\n\nYou should say:\n- Which country it is and where it is located\n- What you know about this country\n- Why you are particularly interested in visiting it\n- And explain what you would do or see there.",
            ],
            [
                'title'   => 'Describe an important decision you made',
                'content' => "Describe an important decision you made in your life.\n\nYou should say:\n- What the decision was\n- When and why you had to make it\n- How difficult the decision was and how you made it\n- And explain the outcome and how it affected your life.",
            ],
            [
                'title'   => 'Describe a piece of music that is special to you',
                'content' => "Describe a piece of music or a song that is particularly meaningful to you.\n\nYou should say:\n- What the music or song is\n- When you first heard it\n- Why this piece of music is special to you\n- And explain how it makes you feel when you hear it.",
            ],
            [
                'title'   => 'Describe a challenge you successfully overcame',
                'content' => "Describe a challenging situation you faced and successfully overcame.\n\nYou should say:\n- What the challenge was and when it occurred\n- Why it was particularly difficult\n- What steps you took to overcome it\n- And explain what you learned from this experience.",
            ],
            [
                'title'   => 'Describe an environmental problem in your area',
                'content' => "Describe an environmental problem that you are aware of in your local area or country.\n\nYou should say:\n- What the environmental problem is\n- What causes it\n- What effects it is having\n- And explain what you think should be done to solve this problem.",
            ],
            [
                'title'   => 'Describe a teacher who influenced you',
                'content' => "Describe a teacher who had a significant influence on your life.\n\nYou should say:\n- Who this teacher was and what they taught\n- When they taught you\n- What made this teacher different from others\n- And explain how their teaching influenced you.",
            ],
            [
                'title'   => 'Describe a gift you gave or received',
                'content' => "Describe a gift you either gave to someone or received from someone that was particularly meaningful.\n\nYou should say:\n- What the gift was\n- Who gave it to you (or who you gave it to)\n- What the occasion was\n- And explain why this gift was so special or meaningful.",
            ],
            [
                'title'   => 'Describe a website or app you use regularly',
                'content' => "Describe a website or smartphone application that you use regularly.\n\nYou should say:\n- What the website or app is\n- How long you have been using it and how you found out about it\n- What you use it for\n- And explain why you find it so useful or enjoyable.",
            ],
            [
                'title'   => 'Describe a time you worked as part of a team',
                'content' => "Describe a time when you worked as part of a team to achieve a goal.\n\nYou should say:\n- What the team was trying to achieve\n- What your role in the team was\n- What difficulties the team faced\n- And explain whether you felt the experience of working as a team was a positive one.",
            ],
        ];

        foreach ($cards as $q) {
            Question::updateOrCreate(
                ['title' => $q['title'], 'category' => 'speaking_part2'],
                ['type' => 'speaking', 'content' => $q['content'], 'active' => true]
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PART 3 — Abstract discussion questions (paired with Part 2 themes)
    // ─────────────────────────────────────────────────────────────────────────
    protected function seedPart3(): void
    {
        $discussions = [
            [
                'title'   => 'Heritage and Cultural Preservation',
                'content' => "1. Why do you think governments invest money in preserving historical buildings and monuments?\n2. Is it more important to preserve old buildings or to build new ones to meet modern needs?\n3. How do historical sites contribute to a country's national identity?\n4. Do you think that virtual tours of historical sites will ever replace physical visits? Why?\n5. Some people argue that money spent on preserving heritage would be better used to solve social problems. What is your view?",
            ],
            [
                'title'   => 'Learning and Education Systems',
                'content' => "1. How important is it for education systems to teach practical skills alongside academic knowledge?\n2. Do you think the way people learn has changed significantly with the development of technology?\n3. How should education systems prepare young people for the job market of the future?\n4. Is it true that some people are simply 'not suited' to formal education? Why do you think this?\n5. How can governments encourage lifelong learning among adults?",
            ],
            [
                'title'   => 'Helping Others and Volunteerism',
                'content' => "1. Why do you think some people are more willing to help others than others?\n2. Should governments encourage or require citizens to volunteer?\n3. Do you think globalisation has made people more or less willing to help people in other countries?\n4. What role do charities and NGOs play compared to governments in addressing social issues?\n5. Is it possible to be too generous? Can helping others sometimes cause dependency?",
            ],
            [
                'title'   => 'Books, Media and Information',
                'content' => "1. How has the internet changed the way people access information compared to twenty years ago?\n2. Do you think people are more informed or more misinformed today because of social media?\n3. Should governments regulate what kind of content is published on the internet? Why?\n4. Do you think that printed books will eventually disappear?\n5. Is it important for people to be able to identify misinformation? How can this skill be developed?",
            ],
            [
                'title'   => 'Famous People and Role Models',
                'content' => "1. Why do you think people are often fascinated by the lives of celebrities?\n2. Do famous people have a responsibility to set a good example for the public?\n3. Are role models in sport, entertainment, or politics more influential than parents or teachers?\n4. How has social media changed the nature of fame and celebrity?\n5. Do you think it is healthy for young people to have celebrity role models?",
            ],
            [
                'title'   => 'Patience and Modern Life',
                'content' => "1. Do you think modern technology has made people less patient? Why or why not?\n2. In what areas of life do you think patience is particularly important?\n3. How do different cultures view punctuality and waiting differently?\n4. Is impatience always a negative quality, or can it sometimes drive progress?\n5. How does frustration from waiting affect decision-making?",
            ],
            [
                'title'   => 'International Travel and Cultural Understanding',
                'content' => "1. How does travelling to other countries contribute to cultural understanding?\n2. Do you think mass tourism has more positive or negative effects on local cultures?\n3. Should wealthy countries make it easier for people from poorer countries to travel and work abroad?\n4. Is it possible to truly understand another culture without living in that country?\n5. How has increased global travel affected the concept of national identity?",
            ],
            [
                'title'   => 'Decision-Making and Risk',
                'content' => "1. Do you think most people make decisions rationally or emotionally? Why?\n2. How does age affect the kinds of decisions people make?\n3. Should young people be encouraged to take more risks in life? Why?\n4. How do cultural factors influence risk tolerance in different societies?\n5. What role should governments play in protecting people from bad decisions?",
            ],
            [
                'title'   => 'Music and Cultural Identity',
                'content' => "1. In what ways does music reflect the culture and values of the society that produces it?\n2. How has globalisation affected traditional or folk music in different countries?\n3. Should governments fund traditional music and arts? Why?\n4. Do you think music can be an effective tool for social or political change?\n5. Is it possible for music to transcend language and cultural barriers?",
            ],
            [
                'title'   => 'Resilience and Success',
                'content' => "1. Do you think success is primarily the result of talent or hard work?\n2. How important is failure in the process of achieving success?\n3. Do you think education systems adequately prepare young people to deal with failure and setbacks?\n4. Are some people naturally more resilient than others, or is resilience a skill that can be learned?\n5. How does societal pressure to succeed affect the mental health of young people?",
            ],
            [
                'title'   => 'Environmental Responsibility',
                'content' => "1. Who has more responsibility for protecting the environment — individuals, businesses, or governments?\n2. Do you think environmental problems can be solved through technology alone?\n3. How effective are international agreements such as the Paris Agreement at addressing climate change?\n4. Should people who damage the environment face heavier legal penalties?\n5. Can economic development and environmental protection be achieved simultaneously?",
            ],
            [
                'title'   => 'The Role of Teachers in Society',
                'content' => "1. Do you think teachers are valued sufficiently in your society?\n2. How has the role of the teacher changed with the rise of the internet and online learning?\n3. Should teachers be paid according to how well their students perform? Why?\n4. How important is the relationship between teacher and student to effective learning?\n5. Do you think parents or schools have a greater influence on a child's development?",
            ],
            [
                'title'   => 'Gifts, Consumerism and Happiness',
                'content' => "1. Do you think material gifts are a good way to express appreciation or affection?\n2. How has consumerism affected people's expectations around celebrations and gift-giving?\n3. Research suggests that experiences (like travel) bring more lasting happiness than material objects. Do you agree?\n4. Should governments do more to discourage excessive consumption? How?\n5. Is the pressure to give expensive gifts at celebrations (weddings, birthdays) healthy for society?",
            ],
            [
                'title'   => 'Technology and Human Connection',
                'content' => "1. Has technology made it easier or harder to form and maintain genuine friendships?\n2. Do you think people who grow up with smartphones have fundamentally different social skills from previous generations?\n3. Should children be restricted from using the internet and smartphones? Until what age?\n4. How can companies that make social media platforms be held responsible for the effects on users?\n5. Is it possible to have a meaningful friendship with someone you have only ever communicated with online?",
            ],
            [
                'title'   => 'Teamwork and Individual Achievement',
                'content' => "1. In what fields do you think teamwork is more important than individual achievement?\n2. How do cultural values affect attitudes towards teamwork and individual recognition?\n3. Do you think schools do enough to teach collaborative skills?\n4. Can a team ever perform better than the sum of its individual members? What conditions are required?\n5. Should workplaces reward teams or individuals? What are the advantages and disadvantages of each approach?",
            ],
        ];

        foreach ($discussions as $q) {
            Question::updateOrCreate(
                ['title' => $q['title'], 'category' => 'speaking_part3'],
                ['type' => 'speaking', 'content' => $q['content'], 'active' => true]
            );
        }
    }
}
