<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class ListeningReadingSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedListening();
        $this->seedReading();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // LISTENING
    // ──────────────────────────────────────────────────────────────────────────
    private function seedListening(): void
    {
        $sets = [
            // ── Academic Set 1: covers fill, mcq, mcq_multi, matching_item, diagram_label ──
            [
                'type'     => 'listening',
                'category' => 'listening_academic',
                'title'    => 'University Orientation & Renewable Energy Lecture',
                'content'  => '4-section academic listening test: university orientation, campus tour, student project discussion, and a lecture on renewable energy.',
                'active'   => true,
                'metadata' => json_encode([
                    'audio_url' => null,
                    'questions' => [
                        // ── Section 1 — Social conversation (fill, mcq) ────────────────
                        ['id'=>'L1Q1', 'section'=>1, 'type'=>'fill',
                         'question'=>'The student wants to register for the ________ department.',
                         'answer'=>'engineering'],
                        ['id'=>'L1Q2', 'section'=>1, 'type'=>'fill',
                         'question'=>'The orientation starts at ________ on Monday.',
                         'answer'=>'9:30'],
                        ['id'=>'L1Q3', 'section'=>1, 'type'=>'fill',
                         'question'=>'The student should bring her ________ card to the first session.',
                         'answer'=>'student'],
                        ['id'=>'L1Q4', 'section'=>1, 'type'=>'mcq',
                         'question'=>'Where is the registration office located?',
                         'options'=>['A. Ground floor, main building','B. Second floor, east wing','C. Library basement','D. Student union'],
                         'answer'=>'B. Second floor, east wing'],
                        ['id'=>'L1Q5', 'section'=>1, 'type'=>'mcq',
                         'question'=>'What time does the cafeteria open?',
                         'options'=>['A. 7:00 AM','B. 7:30 AM','C. 8:00 AM','D. 8:30 AM'],
                         'answer'=>'A. 7:00 AM'],
                        ['id'=>'L1Q6', 'section'=>1, 'type'=>'fill',
                         'question'=>"The student's ID number begins with the letters ________.",
                         'answer'=>'ENG'],
                        ['id'=>'L1Q7', 'section'=>1, 'type'=>'fill',
                         'question'=>'Students must complete the online form by ________ October.',
                         'answer'=>'15th'],
                        ['id'=>'L1Q8', 'section'=>1, 'type'=>'fill',
                         'question'=>"The student's assigned tutor is Dr ________.",
                         'answer'=>'Martinez'],
                        ['id'=>'L1Q9', 'section'=>1, 'type'=>'fill',
                         'question'=>'The sports centre is open until ________ on weekdays.',
                         'answer'=>'10 pm'],
                        ['id'=>'L1Q10', 'section'=>1, 'type'=>'mcq',
                         'question'=>'Which document is NOT required for registration?',
                         'options'=>['A. Passport','B. Bank statement','C. Academic transcript','D. Medical certificate'],
                         'answer'=>'D. Medical certificate'],

                        // ── Section 2 — Monologue: campus map (diagram_label + fill) ──
                        ['id'=>'L2D1', 'section'=>2, 'type'=>'diagram_label',
                         'question'=>'Label the campus map. Write ONE OR TWO WORDS for each answer.',
                         'description'=>'The speaker describes the location of key buildings. Use the clues to complete the labels.',
                         'labels'=>[
                             ['key'=>'L2D1_A', 'hint'=>'Large building with books, next to the main gate', 'answer'=>'library'],
                             ['key'=>'L2D1_B', 'hint'=>'Building where students eat meals', 'answer'=>'cafeteria'],
                             ['key'=>'L2D1_C', 'hint'=>'Building where international students register', 'answer'=>'student services'],
                             ['key'=>'L2D1_D', 'hint'=>'Open area in the centre of campus', 'answer'=>'quadrangle'],
                         ]],
                        ['id'=>'L2Q11', 'section'=>2, 'type'=>'fill',
                         'question'=>'The library was built in ________.',
                         'answer'=>'2019'],
                        ['id'=>'L2Q12', 'section'=>2, 'type'=>'fill',
                         'question'=>'The library holds over ________ volumes.',
                         'answer'=>'500,000'],
                        ['id'=>'L2Q13', 'section'=>2, 'type'=>'mcq',
                         'question'=>'Which floor has the silent study area?',
                         'options'=>['A. First','B. Second','C. Third','D. Fourth'],
                         'answer'=>'C. Third'],
                        ['id'=>'L2Q14', 'section'=>2, 'type'=>'fill',
                         'question'=>'The student accommodation costs £________ per week.',
                         'answer'=>'145'],
                        ['id'=>'L2Q15', 'section'=>2, 'type'=>'mcq',
                         'question'=>'What is the main focus of the campus sustainability plan?',
                         'options'=>['A. Reducing plastic waste','B. Achieving carbon neutrality','C. Installing solar panels','D. Expanding green spaces'],
                         'answer'=>'B. Achieving carbon neutrality'],

                        // ── Section 3 — Group discussion: matching (who says what) ────
                        ['id'=>'L3M1', 'section'=>3, 'type'=>'matching_item',
                         'group'=>'L3_speakers',
                         'group_question'=>'Which speaker (Maya, Daniel, or Both) makes each statement? Write the correct name.',
                         'options'=>['Maya','Daniel','Both'],
                         'question'=>'The literature review should be completed first.',
                         'answer'=>'Maya'],
                        ['id'=>'L3M2', 'section'=>3, 'type'=>'matching_item',
                         'group'=>'L3_speakers',
                         'group_question'=>'Which speaker (Maya, Daniel, or Both) makes each statement? Write the correct name.',
                         'options'=>['Maya','Daniel','Both'],
                         'question'=>'The survey needs at least 20 participants.',
                         'answer'=>'Both'],
                        ['id'=>'L3M3', 'section'=>3, 'type'=>'matching_item',
                         'group'=>'L3_speakers',
                         'group_question'=>'Which speaker (Maya, Daniel, or Both) makes each statement? Write the correct name.',
                         'options'=>['Maya','Daniel','Both'],
                         'question'=>'A case study approach would be most effective.',
                         'answer'=>'Daniel'],
                        ['id'=>'L3M4', 'section'=>3, 'type'=>'matching_item',
                         'group'=>'L3_speakers',
                         'group_question'=>'Which speaker (Maya, Daniel, or Both) makes each statement? Write the correct name.',
                         'options'=>['Maya','Daniel','Both'],
                         'question'=>'The project is worth 40% of the final grade.',
                         'answer'=>'Both'],
                        ['id'=>'L3M5', 'section'=>3, 'type'=>'matching_item',
                         'group'=>'L3_speakers',
                         'group_question'=>'Which speaker (Maya, Daniel, or Both) makes each statement? Write the correct name.',
                         'options'=>['Maya','Daniel','Both'],
                         'question'=>'Finding interview participants is the biggest challenge.',
                         'answer'=>'Maya'],
                        ['id'=>'L3Q26', 'section'=>3, 'type'=>'mcq_multi',
                         'question'=>'Which TWO things does the supervisor recommend for the project?',
                         'options'=>['A. Use qualitative analysis software','B. Include quantitative charts','C. Take a case study approach','D. Expand the literature review','E. Interview school principals only'],
                         'answers'=>['A. Use qualitative analysis software','C. Take a case study approach']],
                        ['id'=>'L3Q27', 'section'=>3, 'type'=>'fill',
                         'question'=>'The students will present their findings on ________.',
                         'answer'=>'Friday'],
                        ['id'=>'L3Q28', 'section'=>3, 'type'=>'sentence_completion',
                         'question'=>'The maximum word count for the written report is ________ words.',
                         'answer'=>'5,000'],
                        ['id'=>'L3Q29', 'section'=>3, 'type'=>'short_answer',
                         'question'=>'What is the name of the analysis software the supervisor recommended? (ONE WORD)',
                         'answer'=>'qualitative'],
                        ['id'=>'L3Q30', 'section'=>3, 'type'=>'fill',
                         'question'=>'The project deadline is the ________ of November.',
                         'answer'=>'28th'],

                        // ── Section 4 — Academic lecture (note_completion, flow_chart, mcq) ─
                        ['id'=>'L4N1', 'section'=>4, 'type'=>'note_completion',
                         'context'=>'Notes on renewable energy lecture:',
                         'question'=>'Main topic: ________ energy transitions in developing countries.',
                         'answer'=>'renewable'],
                        ['id'=>'L4N2', 'section'=>4, 'type'=>'note_completion',
                         'context'=>'Notes on renewable energy lecture:',
                         'question'=>'Solar costs have fallen by ________% in the last decade.',
                         'answer'=>'89'],
                        ['id'=>'L4N3', 'section'=>4, 'type'=>'note_completion',
                         'context'=>'Notes on renewable energy lecture:',
                         'question'=>'Main barrier to adoption: ________ infrastructure.',
                         'answer'=>'grid'],
                        ['id'=>'L4F1', 'section'=>4, 'type'=>'flow_chart',
                         'context'=>'Energy transition process: Research → Policy → Investment → ________ → Public adoption',
                         'question'=>'What stage comes after Investment? (ONE WORD)',
                         'answer'=>'deployment'],
                        ['id'=>'L4Q35', 'section'=>4, 'type'=>'mcq',
                         'question'=>'What does the lecturer say about offshore wind energy?',
                         'options'=>['A. It is too expensive to develop','B. It has greater potential than onshore','C. It is unreliable in tropical regions','D. It requires too much land'],
                         'answer'=>'B. It has greater potential than onshore'],
                        ['id'=>'L4Q36', 'section'=>4, 'type'=>'fill',
                         'question'=>'By 2050, renewables could provide ________% of global electricity.',
                         'answer'=>'85'],
                        ['id'=>'L4Q37', 'section'=>4, 'type'=>'mcq',
                         'question'=>'What is described as the "missing link" in the energy transition?',
                         'options'=>['A. Political will','B. Storage technology','C. Public awareness','D. International cooperation'],
                         'answer'=>'B. Storage technology'],
                        ['id'=>'L4Q38', 'section'=>4, 'type'=>'fill',
                         'question'=>'The research paper recommended is by Professor ________.',
                         'answer'=>'Nakamura'],
                        ['id'=>'L4Q39', 'section'=>4, 'type'=>'mcq_multi',
                         'question'=>'Which TWO factors does the lecturer say are essential for a successful energy transition?',
                         'options'=>['A. Political will','B. Storage technology','C. Public protests','D. Lower oil prices','E. Urban planning reform'],
                         'answers'=>['A. Political will','B. Storage technology']],
                        ['id'=>'L4Q40', 'section'=>4, 'type'=>'short_answer',
                         'question'=>'By which year could renewables provide 85% of global electricity? (FOUR DIGITS)',
                         'answer'=>'2050'],
                    ],
                ]),
            ],

            // ── General Training Set 1: covers summary_completion, sentence_completion, mcq ──
            [
                'type'     => 'listening',
                'category' => 'listening_general',
                'title'    => 'Rental Inquiry & Community Centre Programme',
                'content'  => '4-section general training listening test: apartment rental, community centre facilities, workplace induction, and a radio programme on urban farming.',
                'active'   => true,
                'metadata' => json_encode([
                    'audio_url' => null,
                    'questions' => [
                        // Section 1 — Apartment rental enquiry (fill, mcq)
                        ['id'=>'G1Q1', 'section'=>1, 'type'=>'fill',
                         'question'=>'The apartment is located on ________ Street.',
                         'answer'=>'Maple'],
                        ['id'=>'G1Q2', 'section'=>1, 'type'=>'fill',
                         'question'=>'The monthly rent is £________ per month.',
                         'answer'=>'950'],
                        ['id'=>'G1Q3', 'section'=>1, 'type'=>'mcq',
                         'question'=>'What is included in the rent?',
                         'options'=>['A. All utility bills','B. Water and internet only','C. No utilities','D. Electricity only'],
                         'answer'=>'B. Water and internet only'],
                        ['id'=>'G1Q4', 'section'=>1, 'type'=>'fill',
                         'question'=>'The deposit required is ________ weeks\' rent.',
                         'answer'=>'6'],
                        ['id'=>'G1Q5', 'section'=>1, 'type'=>'fill',
                         'question'=>'The apartment is available from ________ March.',
                         'answer'=>'1st'],
                        ['id'=>'G1Q6', 'section'=>1, 'type'=>'mcq',
                         'question'=>'Which of the following is NOT permitted in the apartment?',
                         'options'=>['A. Pets','B. Guests','C. Smoking','D. Home working'],
                         'answer'=>'C. Smoking'],
                        ['id'=>'G1Q7', 'section'=>1, 'type'=>'fill',
                         'question'=>'The nearest underground station is ________ minutes away on foot.',
                         'answer'=>'8'],
                        ['id'=>'G1Q8', 'section'=>1, 'type'=>'fill',
                         'question'=>'Viewings must be arranged by calling ________ before 5 PM.',
                         'answer'=>'07700 900 145'],
                        ['id'=>'G1Q9', 'section'=>1, 'type'=>'mcq',
                         'question'=>'How long is the minimum lease?',
                         'options'=>['A. 3 months','B. 6 months','C. 12 months','D. 24 months'],
                         'answer'=>'C. 12 months'],
                        ['id'=>'G1Q10', 'section'=>1, 'type'=>'fill',
                         'question'=>'The landlord\'s name is Mr ________.',
                         'answer'=>'Thompson'],

                        // Section 2 — Community centre (matching, note_completion)
                        ['id'=>'G2M1', 'section'=>2, 'type'=>'matching_item',
                         'group'=>'G2_days',
                         'group_question'=>'On which day does each activity take place? Choose from: Monday / Wednesday / Friday / Saturday.',
                         'options'=>['Monday','Wednesday','Friday','Saturday'],
                         'question'=>'Yoga class',
                         'answer'=>'Monday'],
                        ['id'=>'G2M2', 'section'=>2, 'type'=>'matching_item',
                         'group'=>'G2_days',
                         'group_question'=>'On which day does each activity take place? Choose from: Monday / Wednesday / Friday / Saturday.',
                         'options'=>['Monday','Wednesday','Friday','Saturday'],
                         'question'=>'Cooking workshop',
                         'answer'=>'Wednesday'],
                        ['id'=>'G2M3', 'section'=>2, 'type'=>'matching_item',
                         'group'=>'G2_days',
                         'group_question'=>'On which day does each activity take place? Choose from: Monday / Wednesday / Friday / Saturday.',
                         'options'=>['Monday','Wednesday','Friday','Saturday'],
                         'question'=>'Film screening',
                         'answer'=>'Friday'],
                        ['id'=>'G2M4', 'section'=>2, 'type'=>'matching_item',
                         'group'=>'G2_days',
                         'group_question'=>'On which day does each activity take place? Choose from: Monday / Wednesday / Friday / Saturday.',
                         'options'=>['Monday','Wednesday','Friday','Saturday'],
                         'question'=>'Children\'s art class',
                         'answer'=>'Saturday'],
                        ['id'=>'G2N1', 'section'=>2, 'type'=>'note_completion',
                         'context'=>'Community centre membership notes:',
                         'question'=>'Annual membership fee: £________',
                         'answer'=>'45'],
                        ['id'=>'G2N2', 'section'=>2, 'type'=>'note_completion',
                         'context'=>'Community centre membership notes:',
                         'question'=>'Concession rate available for students and ________ over 65.',
                         'answer'=>'residents'],
                        ['id'=>'G2Q7', 'section'=>2, 'type'=>'mcq',
                         'question'=>'Where can members park their bicycles?',
                         'options'=>['A. Main entrance','B. Behind the hall','C. On the street','D. Underground car park'],
                         'answer'=>'B. Behind the hall'],
                        ['id'=>'G2Q8', 'section'=>2, 'type'=>'fill',
                         'question'=>'Members can book facilities up to ________ weeks in advance.',
                         'answer'=>'2'],
                        ['id'=>'G2Q9', 'section'=>2, 'type'=>'mcq_multi',
                         'question'=>'Which TWO facilities are available to non-members?',
                         'options'=>['A. Gym','B. Café','C. Swimming pool','D. Library','E. Meeting rooms'],
                         'answers'=>['B. Café','D. Library']],
                        ['id'=>'G2Q10', 'section'=>2, 'type'=>'fill',
                         'question'=>'The centre is closed on ________ each week.',
                         'answer'=>'Sunday'],

                        // Section 3 — Workplace induction (sentence_completion, summary_completion)
                        ['id'=>'G3S1', 'section'=>3, 'type'=>'sentence_completion',
                         'question'=>'All new employees must complete the online ________ training before their first shift.',
                         'answer'=>'safety'],
                        ['id'=>'G3S2', 'section'=>3, 'type'=>'sentence_completion',
                         'question'=>'The company provides a ________ subsidy for employees who use public transport.',
                         'answer'=>'travel'],
                        ['id'=>'G3S3', 'section'=>3, 'type'=>'sentence_completion',
                         'question'=>'Annual leave entitlement starts at ________ days per year.',
                         'answer'=>'25'],
                        ['id'=>'G3Sum1', 'section'=>3, 'type'=>'summary_completion',
                         'context'=>'Summary: The company\'s appraisal system runs every ________ months.',
                         'question'=>'How often are appraisals conducted? (NUMBER)',
                         'answer'=>'6'],
                        ['id'=>'G3Sum2', 'section'=>3, 'type'=>'summary_completion',
                         'context'=>'Summary: Staff who complete more than 3 years of service receive a ________ bonus.',
                         'question'=>'What do long-service staff receive? (ONE WORD)',
                         'answer'=>'loyalty'],
                        ['id'=>'G3Q6', 'section'=>3, 'type'=>'mcq',
                         'question'=>'What is the company\'s policy on flexible working?',
                         'options'=>['A. Not permitted at all','B. Allowed after 3 months\' service','C. Available immediately for all staff','D. Only for part-time employees'],
                         'answer'=>'B. Allowed after 3 months\' service'],
                        ['id'=>'G3Q7', 'section'=>3, 'type'=>'fill',
                         'question'=>'The HR department is located on the ________ floor.',
                         'answer'=>'second'],
                        ['id'=>'G3Q8', 'section'=>3, 'type'=>'mcq_multi',
                         'question'=>'Which TWO benefits are part of the standard employment package?',
                         'options'=>['A. Private health insurance','B. Free gym membership','C. Pension contributions','D. Company car','E. Annual bonus'],
                         'answers'=>['A. Private health insurance','C. Pension contributions']],
                        ['id'=>'G3Q9', 'section'=>3, 'type'=>'fill',
                         'question'=>'The induction period lasts ________ weeks.',
                         'answer'=>'4'],
                        ['id'=>'G3Q10', 'section'=>3, 'type'=>'short_answer',
                         'question'=>'Who should new employees contact if they have a payroll query? (ONE WORD — job title)',
                         'answer'=>'payroll'],

                        // Section 4 — Radio programme on urban farming (fill, mcq, flow_chart)
                        ['id'=>'G4Q1', 'section'=>4, 'type'=>'fill',
                         'question'=>'Urban farms currently supply about ________% of city food needs globally.',
                         'answer'=>'10'],
                        ['id'=>'G4Q2', 'section'=>4, 'type'=>'mcq',
                         'question'=>'What is cited as the main advantage of rooftop farms?',
                         'options'=>['A. Lower water usage','B. No need for soil','C. Reduced food miles','D. Cheaper setup costs'],
                         'answer'=>'C. Reduced food miles'],
                        ['id'=>'G4Q3', 'section'=>4, 'type'=>'fill',
                         'question'=>'Singapore\'s urban farms produce approximately ________ tonnes of vegetables annually.',
                         'answer'=>'1,500'],
                        ['id'=>'G4F1', 'section'=>4, 'type'=>'flow_chart',
                         'context'=>'Urban farm setup process: Site survey → Planning permission → ________ → Planting → Harvest',
                         'question'=>'What stage comes between Planning permission and Planting? (ONE WORD)',
                         'answer'=>'construction'],
                        ['id'=>'G4Q5', 'section'=>4, 'type'=>'mcq',
                         'question'=>'According to the speaker, what is the biggest challenge for urban farming?',
                         'options'=>['A. Lack of sunlight','B. Water costs','C. High land values','D. Public acceptance'],
                         'answer'=>'C. High land values'],
                        ['id'=>'G4Q6', 'section'=>4, 'type'=>'fill',
                         'question'=>'The city mentioned as the world leader in urban farming is ________.',
                         'answer'=>'Singapore'],
                        ['id'=>'G4Q7', 'section'=>4, 'type'=>'mcq_multi',
                         'question'=>'Which TWO crops are most commonly grown on urban rooftop farms?',
                         'options'=>['A. Wheat','B. Leafy greens','C. Tomatoes','D. Maize','E. Potatoes'],
                         'answers'=>['B. Leafy greens','C. Tomatoes']],
                        ['id'=>'G4Q8', 'section'=>4, 'type'=>'sentence_completion',
                         'question'=>'Urban farms can reduce city ________ emissions by cutting transport of food.',
                         'answer'=>'carbon'],
                        ['id'=>'G4Q9', 'section'=>4, 'type'=>'fill',
                         'question'=>'The programme\'s website address is www.________farm.org',
                         'answer'=>'urban'],
                        ['id'=>'G4Q10', 'section'=>4, 'type'=>'short_answer',
                         'question'=>'Name the city that plans to produce 30% of its food locally by 2030. (ONE WORD)',
                         'answer'=>'Amsterdam'],
                    ],
                ]),
            ],
        ];

        foreach ($sets as $q) {
            Question::firstOrCreate(
                ['title' => $q['title'], 'type' => $q['type']],
                $q
            );
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // READING
    // ──────────────────────────────────────────────────────────────────────────
    private function seedReading(): void
    {
        $passage1 = <<<PASSAGE
The Rise of Urban Vertical Forests

In recent decades, architects and urban planners have embraced a radical concept: buildings covered from base to rooftop in living trees and plants. Known as "vertical forests" or bosco verticale, these structures represent a bold attempt to reintroduce nature into the concrete landscapes of modern cities.

The concept gained international attention in 2014 when Stefano Boeri Architetti completed the Bosco Verticale in Milan, Italy. The two residential towers—rising 111 and 76 metres respectively—host approximately 900 trees, 5,000 shrubs, and 11,000 perennial and ground-covering plants across their facades. Each plant is carefully selected based on its tolerance to altitude, wind exposure, and orientation to sunlight.

Proponents argue that vertical forests offer multiple environmental advantages. The vegetation acts as a natural air filter, absorbing carbon dioxide and fine particulate matter while releasing oxygen. Studies conducted on the Milan towers suggest that the plant life processes approximately 30 tonnes of CO2 annually. Additionally, the trees provide natural shade and insulation, reducing energy consumption for cooling by up to 30% during summer months.

The biodiversity benefits are equally significant. Urban areas are notoriously hostile to wildlife, but vertical forests provide refuge for insects, birds, and small mammals. The Milan towers have been colonised by over 1,600 species of birds and insects that rarely inhabit traditional urban buildings. Ecologists have described this as a form of "micro-habitat" creation within the city.

However, the concept is not without its critics. The construction and maintenance costs of vertical forests are substantially higher than conventional buildings. Specialised irrigation systems must be installed to water plants at height, and structural engineers must account for the considerable additional weight—each tree can add several hundred kilograms to a building's load. Maintenance workers, sometimes referred to as "flying gardeners," must rappel down the facades to prune, replace, and monitor plants at considerable risk and expense.

There are also questions about scalability. Milan's Bosco Verticale required an investment of approximately €65 million—a figure that places such development firmly in the luxury residential market. Critics argue that the environmental benefits could be achieved more cost-effectively through conventional urban greening initiatives, such as planting street trees or creating rooftop gardens on existing buildings.

Despite these challenges, the vertical forest concept is spreading. Similar projects have been completed in Singapore, Lausanne, Nanjing, and Utrecht. Urban planners in several other cities are actively studying proposals. Advocates believe that as green building technologies mature and construction costs fall, vertical forests could become a mainstream rather than exceptional feature of urban architecture.

Whether vertical forests represent a genuine solution to urban environmental challenges or merely an expensive architectural novelty remains a matter of debate. What is certain is that they have succeeded in making the idea of "living buildings" part of the mainstream conversation about sustainable cities.
PASSAGE;

        $passage2 = <<<PASSAGE
The Science of Sleep and Memory Consolidation

Sleep has long been considered essential for human health, but recent neuroscientific research has revealed a far more specific and critical function: the consolidation of memories during sleep. Far from being a passive state of rest, sleep involves complex neurological processes that actively reorganise and strengthen the memories formed during waking hours.

The process begins during the rapid eye movement (REM) stage of sleep, which typically occurs in cycles throughout the night. During REM sleep, the brain replays events experienced during the day—a phenomenon known as memory replay. Scientists have demonstrated this using rodent studies in which animals trained to navigate mazes showed the same neural firing patterns during sleep as during the maze navigation itself.

Equally important is slow-wave sleep (SWS), also called deep sleep. Research published in Nature Neuroscience found that during SWS, memories are transferred from the hippocampus—a brain region involved in short-term memory storage—to the neocortex, where they become part of long-term memory. This transfer process, known as "systems consolidation," is disrupted when individuals are deprived of adequate SWS.

The implications for learning are profound. Studies show that students who sleep immediately after studying perform significantly better on tests than those who stay awake. In one landmark experiment at Harvard University, participants who slept after learning a finger-tapping sequence improved their performance by 20% compared to those who remained awake. This advantage persisted even when the awake group was subsequently given a full night's sleep.

Memory consolidation during sleep is not, however, a passive replay of all experiences equally. The sleeping brain appears to actively select which memories to strengthen, prioritising those associated with emotional significance or survival relevance. Research suggests that the neurotransmitter noradrenaline, which is suppressed during REM sleep, plays a key role in this selective process by allowing the emotional content of memories to be processed without the stress response that would normally accompany it.

Chronic sleep deprivation has measurable consequences for memory. Adults sleeping fewer than six hours per night show accelerated hippocampal atrophy—shrinkage of the brain region most critical for new memory formation. This finding has prompted research into links between poor sleep and neurodegenerative conditions including Alzheimer's disease, with some studies identifying disrupted sleep as a potential early biomarker of cognitive decline.

The emerging science of sleep and memory has practical implications for education policy, workplace productivity, and public health. Yet despite widespread awareness of sleep's importance, modern lifestyles increasingly prioritise productivity over rest, with artificial lighting, digital devices, and shift work all disrupting the natural sleep-wake cycle. Researchers argue that society's chronic under-valuation of sleep represents one of the most correctable yet underaddressed risk factors for cognitive decline.
PASSAGE;

        $sets = [
            // ── Academic Set 1: vertical forests ── TFNG, YNGNG, fill, MCQ, heading_match ──
            [
                'type'     => 'reading',
                'category' => 'reading_academic',
                'title'    => 'The Rise of Urban Vertical Forests',
                'content'  => $passage1,
                'active'   => true,
                'metadata' => json_encode([
                    'title'    => 'The Rise of Urban Vertical Forests',
                    'passage'  => $passage1,
                    'questions' => [
                        // True / False / Not Given (Q1-7)
                        ['id'=>'R1Q1','passage'=>1,'type'=>'tfng','question'=>'The Bosco Verticale in Milan was completed before 2014.','answer'=>'False'],
                        ['id'=>'R1Q2','passage'=>1,'type'=>'tfng','question'=>'The taller of the two Milan towers rises to 111 metres.','answer'=>'True'],
                        ['id'=>'R1Q3','passage'=>1,'type'=>'tfng','question'=>'The plants on the Milan towers are randomly selected.','answer'=>'False'],
                        ['id'=>'R1Q4','passage'=>1,'type'=>'tfng','question'=>'Vertical forests can reduce cooling energy consumption by up to 30%.','answer'=>'True'],
                        ['id'=>'R1Q5','passage'=>1,'type'=>'tfng','question'=>'The Milan towers attract over 1,600 species of birds and insects.','answer'=>'True'],
                        ['id'=>'R1Q6','passage'=>1,'type'=>'tfng','question'=>'Vertical forests are cheaper to maintain than conventional buildings.','answer'=>'False'],
                        ['id'=>'R1Q7','passage'=>1,'type'=>'tfng','question'=>'The author believes vertical forests will definitely become mainstream.','answer'=>'Not Given'],

                        // Sentence completion (Q8-10)
                        ['id'=>'R1Q8','passage'=>1,'type'=>'sentence_completion',
                         'question'=>'The plant life on the Milan towers processes approximately ________ tonnes of CO2 annually.',
                         'answer'=>'30'],
                        ['id'=>'R1Q9','passage'=>1,'type'=>'sentence_completion',
                         'question'=>'Maintenance workers who tend to building facades are nicknamed "________ gardeners."',
                         'answer'=>'flying'],
                        ['id'=>'R1Q10','passage'=>1,'type'=>'sentence_completion',
                         'question'=>"Milan's Bosco Verticale required an investment of approximately €________ million.",
                         'answer'=>'65'],

                        // MCQ (Q11-12)
                        ['id'=>'R1Q11','passage'=>1,'type'=>'mcq',
                         'question'=>'What does the word "colonised" (paragraph 4) suggest about the wildlife on the towers?',
                         'options'=>['A. The animals were deliberately introduced','B. The animals established themselves naturally','C. The animals damaged the plant life','D. The animals are rare species'],
                         'answer'=>'B. The animals established themselves naturally'],
                        ['id'=>'R1Q12','passage'=>1,'type'=>'mcq',
                         'question'=>'According to the passage, which best describes the current status of vertical forests?',
                         'options'=>['A. A proven solution widely adopted globally','B. An experimental idea abandoned after Milan','C. A growing but debated concept spreading internationally','D. A concept limited to European cities'],
                         'answer'=>'C. A growing but debated concept spreading internationally'],

                        // MCQ multi (Q13)
                        ['id'=>'R1Q13','passage'=>1,'type'=>'mcq_multi',
                         'question'=>'Which TWO environmental benefits of vertical forests are mentioned in the passage?',
                         'options'=>['A. Reducing noise pollution','B. Absorbing carbon dioxide','C. Providing wildlife habitat','D. Cooling ocean temperatures','E. Filtering groundwater'],
                         'answers'=>['B. Absorbing carbon dioxide','C. Providing wildlife habitat']],

                        // Yes / No / Not Given (Q14-17)
                        ['id'=>'R1Q14','passage'=>1,'type'=>'yngng','question'=>'The author implies that the vertical forest debate will continue.','answer'=>'Yes'],
                        ['id'=>'R1Q15','passage'=>1,'type'=>'yngng','question'=>'The author agrees with critics who say vertical forests are too expensive.','answer'=>'Not Given'],
                        ['id'=>'R1Q16','passage'=>1,'type'=>'yngng','question'=>'The author suggests that Stefano Boeri\'s concept was poorly executed.','answer'=>'No'],
                        ['id'=>'R1Q17','passage'=>1,'type'=>'yngng','question'=>'The author believes conventional greening initiatives are completely ineffective.','answer'=>'No'],

                        // Heading match (Q18-20)
                        ['id'=>'R1H1','passage'=>1,'type'=>'heading_match',
                         'group'=>'R1_headings',
                         'group_question'=>'Choose the most suitable heading for each paragraph from the list below.',
                         'options'=>['i. Environmental credentials and their evidence','ii. Challenges of cost and maintenance','iii. The origins of a bold architectural idea','iv. Biodiversity benefits in urban spaces','v. International spread and future prospects'],
                         'question'=>'Paragraph 2 (beginning "The concept gained international attention...")',
                         'answer'=>'iii. The origins of a bold architectural idea'],
                        ['id'=>'R1H2','passage'=>1,'type'=>'heading_match',
                         'group'=>'R1_headings',
                         'group_question'=>'Choose the most suitable heading for each paragraph from the list below.',
                         'options'=>['i. Environmental credentials and their evidence','ii. Challenges of cost and maintenance','iii. The origins of a bold architectural idea','iv. Biodiversity benefits in urban spaces','v. International spread and future prospects'],
                         'question'=>'Paragraph 4 (beginning "The biodiversity benefits...")',
                         'answer'=>'iv. Biodiversity benefits in urban spaces'],
                        ['id'=>'R1H3','passage'=>1,'type'=>'heading_match',
                         'group'=>'R1_headings',
                         'group_question'=>'Choose the most suitable heading for each paragraph from the list below.',
                         'options'=>['i. Environmental credentials and their evidence','ii. Challenges of cost and maintenance','iii. The origins of a bold architectural idea','iv. Biodiversity benefits in urban spaces','v. International spread and future prospects'],
                         'question'=>'Paragraph 5 (beginning "However, the concept is not...")',
                         'answer'=>'ii. Challenges of cost and maintenance'],
                    ],
                ]),
            ],

            // ── Academic Set 2: sleep & memory ── TFNG, fill, feature_match, sentence_ending, diagram_label ──
            [
                'type'     => 'reading',
                'category' => 'reading_academic',
                'title'    => 'Sleep and Memory Consolidation',
                'content'  => $passage2,
                'active'   => true,
                'metadata' => json_encode([
                    'title'    => 'The Science of Sleep and Memory Consolidation',
                    'passage'  => $passage2,
                    'questions' => [
                        // TFNG (Q1-5)
                        ['id'=>'R2Q1','passage'=>1,'type'=>'tfng','question'=>'REM sleep is the only stage of sleep involved in memory consolidation.','answer'=>'False'],
                        ['id'=>'R2Q2','passage'=>1,'type'=>'tfng','question'=>'Rodent studies showed the same neural patterns during sleep as during maze navigation.','answer'=>'True'],
                        ['id'=>'R2Q3','passage'=>1,'type'=>'tfng','question'=>'The hippocampus is responsible for long-term memory storage.','answer'=>'False'],
                        ['id'=>'R2Q4','passage'=>1,'type'=>'tfng','question'=>'All memories receive equal strengthening during sleep.','answer'=>'False'],
                        ['id'=>'R2Q5','passage'=>1,'type'=>'tfng','question'=>'Noradrenaline is elevated during REM sleep.','answer'=>'False'],

                        // Sentence completion (Q6-8)
                        ['id'=>'R2Q6','passage'=>1,'type'=>'sentence_completion',
                         'question'=>'During slow-wave sleep, memories are transferred from the hippocampus to the ________ for long-term storage.',
                         'answer'=>'neocortex'],
                        ['id'=>'R2Q7','passage'=>1,'type'=>'sentence_completion',
                         'question'=>'In the Harvard experiment, participants who slept after learning improved their performance by ________%.',
                         'answer'=>'20'],
                        ['id'=>'R2Q8','passage'=>1,'type'=>'sentence_completion',
                         'question'=>'Adults sleeping fewer than six hours per night show accelerated ________ atrophy.',
                         'answer'=>'hippocampal'],

                        // Feature match: which stage of sleep (Q9-12)
                        ['id'=>'R2F1','passage'=>1,'type'=>'feature_match',
                         'group'=>'R2_sleep_stages',
                         'group_question'=>'Which stage of sleep (A. REM sleep, B. Slow-wave sleep, C. Both) is associated with each of the following?',
                         'options'=>['A. REM sleep','B. Slow-wave sleep','C. Both'],
                         'question'=>'Memory replay of daytime events',
                         'answer'=>'A. REM sleep'],
                        ['id'=>'R2F2','passage'=>1,'type'=>'feature_match',
                         'group'=>'R2_sleep_stages',
                         'group_question'=>'Which stage of sleep (A. REM sleep, B. Slow-wave sleep, C. Both) is associated with each of the following?',
                         'options'=>['A. REM sleep','B. Slow-wave sleep','C. Both'],
                         'question'=>'Transfer of memories to the neocortex',
                         'answer'=>'B. Slow-wave sleep'],
                        ['id'=>'R2F3','passage'=>1,'type'=>'feature_match',
                         'group'=>'R2_sleep_stages',
                         'group_question'=>'Which stage of sleep (A. REM sleep, B. Slow-wave sleep, C. Both) is associated with each of the following?',
                         'options'=>['A. REM sleep','B. Slow-wave sleep','C. Both'],
                         'question'=>'Suppression of noradrenaline',
                         'answer'=>'A. REM sleep'],
                        ['id'=>'R2F4','passage'=>1,'type'=>'feature_match',
                         'group'=>'R2_sleep_stages',
                         'group_question'=>'Which stage of sleep (A. REM sleep, B. Slow-wave sleep, C. Both) is associated with each of the following?',
                         'options'=>['A. REM sleep','B. Slow-wave sleep','C. Both'],
                         'question'=>'Disruption leads to poor long-term memory formation',
                         'answer'=>'B. Slow-wave sleep'],

                        // Sentence endings (Q13-15)
                        ['id'=>'R2SE1','passage'=>1,'type'=>'sentence_ending',
                         'group'=>'R2_endings',
                         'group_question'=>'Complete each sentence with the correct ending A–E.',
                         'options'=>[
                             'A. ...showed higher anxiety levels the following day.',
                             'B. ...performed better than those who stayed awake.',
                             'C. ...develop hippocampal atrophy more rapidly.',
                             'D. ...prioritises emotionally significant memories.',
                             'E. ...is linked to Alzheimer\'s disease risk.',
                         ],
                         'question'=>'Students who slept immediately after studying',
                         'answer'=>'B. ...performed better than those who stayed awake.'],
                        ['id'=>'R2SE2','passage'=>1,'type'=>'sentence_ending',
                         'group'=>'R2_endings',
                         'group_question'=>'Complete each sentence with the correct ending A–E.',
                         'options'=>[
                             'A. ...showed higher anxiety levels the following day.',
                             'B. ...performed better than those who stayed awake.',
                             'C. ...develop hippocampal atrophy more rapidly.',
                             'D. ...prioritises emotionally significant memories.',
                             'E. ...is linked to Alzheimer\'s disease risk.',
                         ],
                         'question'=>'Adults who sleep fewer than six hours per night',
                         'answer'=>'C. ...develop hippocampal atrophy more rapidly.'],
                        ['id'=>'R2SE3','passage'=>1,'type'=>'sentence_ending',
                         'group'=>'R2_endings',
                         'group_question'=>'Complete each sentence with the correct ending A–E.',
                         'options'=>[
                             'A. ...showed higher anxiety levels the following day.',
                             'B. ...performed better than those who stayed awake.',
                             'C. ...develop hippocampal atrophy more rapidly.',
                             'D. ...prioritises emotionally significant memories.',
                             'E. ...is linked to Alzheimer\'s disease risk.',
                         ],
                         'question'=>'The sleeping brain selectively strengthens memories and',
                         'answer'=>'D. ...prioritises emotionally significant memories.'],

                        // Diagram label — memory consolidation pathway (Q16)
                        ['id'=>'R2DL1','passage'=>1,'type'=>'diagram_label',
                         'question'=>'Label the memory consolidation pathway. Write NO MORE THAN TWO WORDS for each label.',
                         'description'=>'Refer to the passage to identify each stage in the pathway.',
                         'labels'=>[
                             ['key'=>'R2DL1_A','hint'=>'Where new memories are first stored (short-term)','answer'=>'hippocampus'],
                             ['key'=>'R2DL1_B','hint'=>'Sleep stage during which memory replay occurs','answer'=>'REM sleep'],
                             ['key'=>'R2DL1_C','hint'=>'Brain region where long-term memories are stored','answer'=>'neocortex'],
                             ['key'=>'R2DL1_D','hint'=>'Name of the transfer process from hippocampus to neocortex','answer'=>'systems consolidation'],
                         ]],

                        // Short answer (Q17-18)
                        ['id'=>'R2Q17','passage'=>1,'type'=>'short_answer',
                         'question'=>'Which neurotransmitter is suppressed during REM sleep? (ONE WORD)',
                         'answer'=>'noradrenaline'],
                        ['id'=>'R2Q18','passage'=>1,'type'=>'short_answer',
                         'question'=>'At which university was the finger-tapping sequence experiment conducted? (ONE WORD)',
                         'answer'=>'Harvard'],

                        // MCQ multi (Q19)
                        ['id'=>'R2Q19','passage'=>1,'type'=>'mcq_multi',
                         'question'=>'Which TWO factors are mentioned as disrupting natural sleep cycles in modern life?',
                         'options'=>['A. Artificial lighting','B. Caffeine consumption','C. Digital devices','D. Air conditioning','E. Urban noise'],
                         'answers'=>['A. Artificial lighting','C. Digital devices']],

                        // YNGNG (Q20)
                        ['id'=>'R2Q20','passage'=>1,'type'=>'yngng','question'=>'The author believes society deliberately undervalues sleep.','answer'=>'Not Given'],
                    ],
                ]),
            ],

            // ── General Training: practical letter & notice topics ──
            [
                'type'     => 'reading',
                'category' => 'reading_general',
                'title'    => 'Community Newsletter & Local Council Notice',
                'content'  => "The following texts are from a community newsletter and a local council notice.\n\nTEXT A — Neighbourhood Watch Scheme\nThe Riverside Neighbourhood Watch Scheme is looking for volunteers to help coordinate local safety activities. Established in 2018, the scheme has helped reduce local burglaries by 34%. Volunteers are asked to commit to attending one meeting per month, held on the second Tuesday of each month at the community hall. New members should contact the coordinator, Mrs Patricia Holt, at riverside.watch@email.com or call 01234 567890.\n\nTEXT B — Riverside Library Extended Hours Notice\nFrom 1 March, Riverside Library will extend its opening hours to include Sunday afternoons (2 PM – 6 PM). The extension follows a successful public consultation in which 89% of respondents requested Sunday access. The library's digital lending service, which allows members to borrow e-books and audiobooks, is available 24 hours a day. Members wishing to update their library card should bring two forms of ID to the library during staffed hours.\n\nTEXT C — Spring Recycling Drive\nResidents are invited to dispose of large household items at the council depot on Greenfield Road during the Spring Recycling Drive (15–18 April). Items accepted include furniture, white goods, and garden equipment. Electrical items must have cables attached. Mattresses are not accepted. Residents must book a time slot in advance by calling 01234 112233. The service is free for all registered households.",
                'active'   => true,
                'metadata' => json_encode([
                    'title'    => 'Community Newsletter & Local Council Notice',
                    'passage'  => "The following texts are from a community newsletter and a local council notice.\n\nTEXT A — Neighbourhood Watch Scheme\nThe Riverside Neighbourhood Watch Scheme is looking for volunteers to help coordinate local safety activities. Established in 2018, the scheme has helped reduce local burglaries by 34%. Volunteers are asked to commit to attending one meeting per month, held on the second Tuesday of each month at the community hall. New members should contact the coordinator, Mrs Patricia Holt, at riverside.watch@email.com or call 01234 567890.\n\nTEXT B — Riverside Library Extended Hours Notice\nFrom 1 March, Riverside Library will extend its opening hours to include Sunday afternoons (2 PM – 6 PM). The extension follows a successful public consultation in which 89% of respondents requested Sunday access. The library's digital lending service, which allows members to borrow e-books and audiobooks, is available 24 hours a day. Members wishing to update their library card should bring two forms of ID to the library during staffed hours.\n\nTEXT C — Spring Recycling Drive\nResidents are invited to dispose of large household items at the council depot on Greenfield Road during the Spring Recycling Drive (15–18 April). Items accepted include furniture, white goods, and garden equipment. Electrical items must have cables attached. Mattresses are not accepted. Residents must book a time slot in advance by calling 01234 112233. The service is free for all registered households.",
                    'questions' => [
                        // Text matching — which text (A/B/C)
                        ['id'=>'RG1M1','passage'=>1,'type'=>'feature_match',
                         'group'=>'RG1_texts',
                         'group_question'=>'In which text (A, B, or C) can you find information about each of the following?',
                         'options'=>['A','B','C'],
                         'question'=>'How to dispose of old household appliances',
                         'answer'=>'C'],
                        ['id'=>'RG1M2','passage'=>1,'type'=>'feature_match',
                         'group'=>'RG1_texts',
                         'group_question'=>'In which text (A, B, or C) can you find information about each of the following?',
                         'options'=>['A','B','C'],
                         'question'=>'A service available at any time of day or night',
                         'answer'=>'B'],
                        ['id'=>'RG1M3','passage'=>1,'type'=>'feature_match',
                         'group'=>'RG1_texts',
                         'group_question'=>'In which text (A, B, or C) can you find information about each of the following?',
                         'options'=>['A','B','C'],
                         'question'=>'A reduction in local crime rates',
                         'answer'=>'A'],
                        ['id'=>'RG1M4','passage'=>1,'type'=>'feature_match',
                         'group'=>'RG1_texts',
                         'group_question'=>'In which text (A, B, or C) can you find information about each of the following?',
                         'options'=>['A','B','C'],
                         'question'=>'A requirement to make a booking before attending',
                         'answer'=>'C'],
                        ['id'=>'RG1M5','passage'=>1,'type'=>'feature_match',
                         'group'=>'RG1_texts',
                         'group_question'=>'In which text (A, B, or C) can you find information about each of the following?',
                         'options'=>['A','B','C'],
                         'question'=>'Results of a survey of local people',
                         'answer'=>'B'],

                        // TFNG (Q6-10)
                        ['id'=>'RG1Q6','passage'=>1,'type'=>'tfng','question'=>'The Neighbourhood Watch Scheme was established in 2019.','answer'=>'False'],
                        ['id'=>'RG1Q7','passage'=>1,'type'=>'tfng','question'=>'Volunteers must attend meetings every week.','answer'=>'False'],
                        ['id'=>'RG1Q8','passage'=>1,'type'=>'tfng','question'=>'Riverside Library will be open on Sundays from 1 March.','answer'=>'True'],
                        ['id'=>'RG1Q9','passage'=>1,'type'=>'tfng','question'=>'The Spring Recycling Drive charges a fee for mattress disposal.','answer'=>'Not Given'],
                        ['id'=>'RG1Q10','passage'=>1,'type'=>'tfng','question'=>'Electrical items must have cables attached to be accepted at the depot.','answer'=>'True'],

                        // Sentence completion (Q11-14)
                        ['id'=>'RG1Q11','passage'=>1,'type'=>'sentence_completion',
                         'question'=>'The Neighbourhood Watch Scheme has reduced local burglaries by ________%.',
                         'answer'=>'34'],
                        ['id'=>'RG1Q12','passage'=>1,'type'=>'sentence_completion',
                         'question'=>'Library members who want to update their card must bring ________ forms of ID.',
                         'answer'=>'two'],
                        ['id'=>'RG1Q13','passage'=>1,'type'=>'sentence_completion',
                         'question'=>'The Spring Recycling Drive runs from 15 to ________ April.',
                         'answer'=>'18'],
                        ['id'=>'RG1Q14','passage'=>1,'type'=>'sentence_completion',
                         'question'=>'New members of the Watch scheme should contact Mrs ________ Holt.',
                         'answer'=>'Patricia'],

                        // Short answer (Q15-17)
                        ['id'=>'RG1Q15','passage'=>1,'type'=>'short_answer',
                         'question'=>'What percentage of survey respondents requested Sunday library access? (NUMBER + % SYMBOL)',
                         'answer'=>'89%'],
                        ['id'=>'RG1Q16','passage'=>1,'type'=>'short_answer',
                         'question'=>'On which road is the council depot located? (ONE WORD)',
                         'answer'=>'Greenfield'],
                        ['id'=>'RG1Q17','passage'=>1,'type'=>'short_answer',
                         'question'=>'What type of items are NOT accepted at the Spring Recycling Drive? (ONE WORD)',
                         'answer'=>'Mattresses'],

                        // MCQ (Q18-20)
                        ['id'=>'RG1Q18','passage'=>1,'type'=>'mcq',
                         'question'=>'What is the main purpose of Text A?',
                         'options'=>['A. To warn residents about local crime','B. To recruit volunteers for a community programme','C. To report on a council investigation','D. To advertise a new security company'],
                         'answer'=>'B. To recruit volunteers for a community programme'],
                        ['id'=>'RG1Q19','passage'=>1,'type'=>'mcq',
                         'question'=>'What does the library\'s digital lending service allow members to do?',
                         'options'=>['A. Download free music','B. Book reading rooms online','C. Borrow e-books and audiobooks','D. Access the internet remotely'],
                         'answer'=>'C. Borrow e-books and audiobooks'],
                        ['id'=>'RG1Q20','passage'=>1,'type'=>'mcq',
                         'question'=>'Which item would NOT be accepted at the Spring Recycling Drive?',
                         'options'=>['A. A broken wardrobe','B. A washing machine with its cable','C. A lawnmower','D. A mattress'],
                         'answer'=>'D. A mattress'],
                    ],
                ]),
            ],
        ];

        foreach ($sets as $q) {
            Question::firstOrCreate(
                ['title' => $q['title'], 'type' => $q['type']],
                $q
            );
        }
    }
}
