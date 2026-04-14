<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

/**
 * Adds 3 additional Listening sets + 3 additional Reading sets
 * so users don't always get the same test.
 */
class ListeningReadingExpandedSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedListening();
        $this->seedReading();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LISTENING — 3 new sets
    // ─────────────────────────────────────────────────────────────────────────
    private function seedListening(): void
    {
        $sets = [

            // ── Academic Set 2: Health Science Campus ──
            [
                'title'    => 'Health Science Campus & Medical Research Lecture',
                'category' => 'listening_academic',
                'metadata' => [
                    'audio_url' => null,
                    'questions' => [
                        // Section 1 — Phone enquiry about health clinic registration
                        ['id'=>'hs101','section'=>1,'type'=>'fill','question'=>'The patient registration form must be submitted by _____.','answer'=>'Friday'],
                        ['id'=>'hs102','section'=>1,'type'=>'fill','question'=>'The clinic is located on the _____ floor of the Wellbeing Centre.','answer'=>'third'],
                        ['id'=>'hs103','section'=>1,'type'=>'mcq','question'=>'What document must new patients bring to their first appointment?','options'=>['A student ID card','A passport','A letter from their GP','A health insurance certificate'],'answer'=>'A letter from their GP'],
                        ['id'=>'hs104','section'=>1,'type'=>'fill','question'=>'Appointments for mental health support must be booked at least _____ days in advance.','answer'=>'five'],
                        ['id'=>'hs105','section'=>1,'type'=>'fill','question'=>'The after-hours emergency number is _____.','answer'=>'0800 445 223'],
                        ['id'=>'hs106','section'=>1,'type'=>'tfng','question'=>'The dental service is included in the standard student health package.','answer'=>'False'],
                        ['id'=>'hs107','section'=>1,'type'=>'fill','question'=>'Students can book appointments online using the _____ portal.','answer'=>'MyHealth'],
                        ['id'=>'hs108','section'=>1,'type'=>'mcq','question'=>'How long is a standard GP appointment?','options'=>['10 minutes','15 minutes','20 minutes','30 minutes'],'answer'=>'15 minutes'],
                        ['id'=>'hs109','section'=>1,'type'=>'fill','question'=>'The physiotherapy unit is open from Monday to _____.','answer'=>'Saturday'],
                        ['id'=>'hs110','section'=>1,'type'=>'fill','question'=>'Late cancellations within _____ hours incur a £10 fee.','answer'=>'24'],

                        // Section 2 — Campus health and wellbeing tour
                        ['id'=>'hs201','section'=>2,'type'=>'fill','question'=>'The Wellbeing Garden is located behind the _____ building.','answer'=>'library'],
                        ['id'=>'hs202','section'=>2,'type'=>'mcq','question'=>'What is the main purpose of the Quiet Room?','options'=>['Prayer and meditation','Studying','Mental health support sessions','Sleeping between classes'],'answer'=>'Mental health support sessions'],
                        ['id'=>'hs203','section'=>2,'type'=>'fill','question'=>'The sports complex swimming pool is heated to _____ degrees Celsius.','answer'=>'28'],
                        ['id'=>'hs204','section'=>2,'type'=>'fill','question'=>'The peer support programme was established in _____.','answer'=>'2018'],
                        ['id'=>'hs205','section'=>2,'type'=>'mcq','question'=>'Which of the following activities is offered on Tuesday evenings?','options'=>['Yoga','Mindfulness meditation','Swimming club','Running club'],'answer'=>'Mindfulness meditation'],
                        ['id'=>'hs206','section'=>2,'type'=>'fill','question'=>'The nutrition advice clinic is run by qualified _____.','answer'=>'dietitians'],
                        ['id'=>'hs207','section'=>2,'type'=>'fill','question'=>'Students can borrow exercise equipment for a maximum of _____ days.','answer'=>'seven'],
                        ['id'=>'hs208','section'=>2,'type'=>'mcq','question'=>'What percentage of students use the wellbeing services at least once per term?','options'=>['12%','24%','38%','51%'],'answer'=>'38%'],
                        ['id'=>'hs209','section'=>2,'type'=>'fill','question'=>'The campus stop-smoking programme is offered free of charge in _____ weeks.','answer'=>'six'],
                        ['id'=>'hs210','section'=>2,'type'=>'fill','question'=>'The wellbeing app can be downloaded from the _____ store.','answer'=>'university'],

                        // Section 3 — Student seminar: sleep research project
                        ['id'=>'hs301','section'=>3,'type'=>'mcq','question'=>'What is the main problem the students identify with their research proposal?','options'=>['The sample size is too small','The methodology is unclear','They lack access to participants','The timeline is unrealistic'],'answer'=>'The sample size is too small'],
                        ['id'=>'hs302','section'=>3,'type'=>'fill','question'=>'The students plan to recruit participants using _____ media.','answer'=>'social'],
                        ['id'=>'hs303','section'=>3,'type'=>'mcq_multi','question'=>'Which TWO factors do the students agree will affect their results?','options'=>['Age of participants','Diet and nutrition','Caffeine consumption','Screen time before sleep','Room temperature'],'answers'=>['Caffeine consumption','Screen time before sleep']],
                        ['id'=>'hs304','section'=>3,'type'=>'fill','question'=>'Participants will wear a _____ monitor throughout the study.','answer'=>'sleep'],
                        ['id'=>'hs305','section'=>3,'type'=>'fill','question'=>'The students expect to complete data collection within _____ months.','answer'=>'three'],
                        ['id'=>'hs306','section'=>3,'type'=>'mcq','question'=>'What does the supervisor recommend they add to their literature review?','options'=>['More recent studies from Asia','Statistical analysis methods','A definition of insomnia','Historical background on sleep research'],'answer'=>'More recent studies from Asia'],
                        ['id'=>'hs307','section'=>3,'type'=>'fill','question'=>'The ethical approval form must be submitted to the _____ committee.','answer'=>'research ethics'],
                        ['id'=>'hs308','section'=>3,'type'=>'fill','question'=>'The students will present their findings at the _____ conference in June.','answer'=>'postgraduate'],
                        ['id'=>'hs309','section'=>3,'type'=>'mcq','question'=>'What is the students\' hypothesis about weekend sleep patterns?','options'=>['They are healthier than weekday patterns','They compensate for weekday sleep deprivation','They are unrelated to academic performance','They are longer in first-year students'],'answer'=>'They compensate for weekday sleep deprivation'],
                        ['id'=>'hs310','section'=>3,'type'=>'fill','question'=>'The maximum word count for the research report is _____ words.','answer'=>'8,000'],

                        // Section 4 — Lecture: The Impact of Microplastics on Human Health
                        ['id'=>'hs401','section'=>4,'type'=>'fill','question'=>'Microplastics are defined as plastic particles smaller than _____ millimetres.','answer'=>'5'],
                        ['id'=>'hs402','section'=>4,'type'=>'fill','question'=>'Scientists estimate that an average person consumes approximately _____ microplastic particles per week.','answer'=>'100,000'],
                        ['id'=>'hs403','section'=>4,'type'=>'mcq','question'=>'Where were microplastics first detected in the human body according to the lecturer?','options'=>['The lungs','The liver','The bloodstream','The digestive tract'],'answer'=>'The bloodstream'],
                        ['id'=>'hs404','section'=>4,'type'=>'fill','question'=>'The lecturer describes microplastics found in drinking water as a _____ problem.','answer'=>'global'],
                        ['id'=>'hs405','section'=>4,'type'=>'fill','question'=>'The process by which plastics break down in the environment is called _____.','answer'=>'photodegradation'],
                        ['id'=>'hs406','section'=>4,'type'=>'mcq','question'=>'Which organ system does the lecturer say is most at risk from long-term microplastic exposure?','options'=>['The nervous system','The endocrine system','The cardiovascular system','The musculoskeletal system'],'answer'=>'The endocrine system'],
                        ['id'=>'hs407','section'=>4,'type'=>'fill','question'=>'Bottled water contains _____ times more microplastics than tap water on average.','answer'=>'22'],
                        ['id'=>'hs408','section'=>4,'type'=>'fill','question'=>'The lecturer recommends using _____ filters to reduce microplastic ingestion from tap water.','answer'=>'carbon'],
                        ['id'=>'hs409','section'=>4,'type'=>'mcq','question'=>'What does the lecturer say is the most effective long-term solution to microplastic pollution?','options'=>['Improved filtration systems','Biodegradable packaging','Reducing plastic production','Ocean clean-up technology'],'answer'=>'Reducing plastic production'],
                        ['id'=>'hs410','section'=>4,'type'=>'fill','question'=>'The lecturer states that the health effects of microplastics are described as _____ by most current research.','answer'=>'uncertain'],
                    ],
                ],
            ],

            // ── Academic Set 3: Urban Planning & Architecture ──
            [
                'title'    => 'City Planning Office & Architecture Lecture',
                'category' => 'listening_academic',
                'metadata' => [
                    'audio_url' => null,
                    'questions' => [
                        // Section 1
                        ['id'=>'up101','section'=>1,'type'=>'fill','question'=>'The planning application must include a _____ impact assessment.','answer'=>'environmental'],
                        ['id'=>'up102','section'=>1,'type'=>'fill','question'=>'Public consultations for the new development will be held on _____ Street.','answer'=>'Brook'],
                        ['id'=>'up103','section'=>1,'type'=>'mcq','question'=>'What is the maximum height limit for new residential buildings in the central zone?','options'=>['6 storeys','8 storeys','10 storeys','12 storeys'],'answer'=>'8 storeys'],
                        ['id'=>'up104','section'=>1,'type'=>'fill','question'=>'Applications must be submitted _____ weeks before the committee meeting.','answer'=>'eight'],
                        ['id'=>'up105','section'=>1,'type'=>'fill','question'=>'The planning department is open Monday to Friday, _____ am to 5 pm.','answer'=>'9'],
                        ['id'=>'up106','section'=>1,'type'=>'mcq','question'=>'Which area is designated as a conservation zone?','options'=>['The harbour district','The financial quarter','The old market area','The university precinct'],'answer'=>'The old market area'],
                        ['id'=>'up107','section'=>1,'type'=>'fill','question'=>'New developments must allocate at least _____ percent of floor space to green areas.','answer'=>'15'],
                        ['id'=>'up108','section'=>1,'type'=>'fill','question'=>'The fee for a commercial planning application is £_____.','answer'=>'1,200'],
                        ['id'=>'up109','section'=>1,'type'=>'mcq','question'=>'How long does a standard planning application take to process?','options'=>['4 weeks','8 weeks','12 weeks','16 weeks'],'answer'=>'8 weeks'],
                        ['id'=>'up110','section'=>1,'type'=>'fill','question'=>'Objections to planning decisions can be submitted within _____ days.','answer'=>'28'],

                        // Section 2
                        ['id'=>'up201','section'=>2,'type'=>'fill','question'=>'The new transport hub will be constructed on the site of the former _____ station.','answer'=>'goods'],
                        ['id'=>'up202','section'=>2,'type'=>'mcq','question'=>'What is the estimated cost of the waterfront regeneration project?','options'=>['£45 million','£72 million','£90 million','£120 million'],'answer'=>'£90 million'],
                        ['id'=>'up203','section'=>2,'type'=>'fill','question'=>'The cycle network will extend to _____ kilometres when complete.','answer'=>'240'],
                        ['id'=>'up204','section'=>2,'type'=>'fill','question'=>'Phase two of the development is expected to create _____ permanent jobs.','answer'=>'3,500'],
                        ['id'=>'up205','section'=>2,'type'=>'mcq','question'=>'What percentage of new homes must be classified as affordable housing?','options'=>['20%','25%','30%','40%'],'answer'=>'30%'],
                        ['id'=>'up206','section'=>2,'type'=>'fill','question'=>'The new district library will have a capacity for _____ visitors per day.','answer'=>'800'],
                        ['id'=>'up207','section'=>2,'type'=>'fill','question'=>'Construction on Phase 1 is due to begin in _____.','answer'=>'March'],
                        ['id'=>'up208','section'=>2,'type'=>'mcq','question'=>'Who was the architect commissioned to design the central plaza?','options'=>['Zaha Hadid Architects','Renzo Piano Building Workshop','Foster + Partners','Bjarke Ingels Group'],'answer'=>'Bjarke Ingels Group'],
                        ['id'=>'up209','section'=>2,'type'=>'fill','question'=>'The green roof on the civic centre will reduce energy consumption by _____ percent.','answer'=>'35'],
                        ['id'=>'up210','section'=>2,'type'=>'fill','question'=>'Residents can view the development plans at the _____ Hall every Tuesday.','answer'=>'Town'],

                        // Section 3
                        ['id'=>'up301','section'=>3,'type'=>'mcq','question'=>'What aspect of the sustainable building project are the students most concerned about?','options'=>['The budget','The timeline','Material sourcing','Community consultation'],'answer'=>'Material sourcing'],
                        ['id'=>'up302','section'=>3,'type'=>'fill','question'=>'The students plan to use _____ bricks for the exterior walls.','answer'=>'recycled'],
                        ['id'=>'up303','section'=>3,'type'=>'mcq_multi','question'=>'Which TWO energy sources will the building use?','options'=>['Wind turbines','Geothermal heating','Solar panels','Biomass boilers','Hydrogen fuel cells'],'answers'=>['Geothermal heating','Solar panels']],
                        ['id'=>'up304','section'=>3,'type'=>'fill','question'=>'The building is designed to achieve a _____ energy performance rating.','answer'=>'net zero'],
                        ['id'=>'up305','section'=>3,'type'=>'fill','question'=>'Water recycling will reduce the building\'s consumption by _____ percent.','answer'=>'60'],
                        ['id'=>'up306','section'=>3,'type'=>'mcq','question'=>'What does the tutor suggest they research further?','options'=>['Passive cooling systems','Smart glass technology','Green wall maintenance','Underground water storage'],'answer'=>'Passive cooling systems'],
                        ['id'=>'up307','section'=>3,'type'=>'fill','question'=>'The students must submit their final design by _____ April.','answer'=>'14th'],
                        ['id'=>'up308','section'=>3,'type'=>'fill','question'=>'The project will be assessed by a panel of _____ architects.','answer'=>'industry'],
                        ['id'=>'up309','section'=>3,'type'=>'mcq','question'=>'Which standard will the building comply with?','options'=>['BREEAM Outstanding','LEED Platinum','Passivhaus','WELL Building Standard'],'answer'=>'BREEAM Outstanding'],
                        ['id'=>'up310','section'=>3,'type'=>'fill','question'=>'The model of the building must be built to a scale of _____.','answer'=>'1:50'],

                        // Section 4 — Lecture: History of Urban Planning
                        ['id'=>'up401','section'=>4,'type'=>'fill','question'=>'The first planned city in recorded history is believed to be _____.','answer'=>'Mohenjo-daro'],
                        ['id'=>'up402','section'=>4,'type'=>'fill','question'=>'Baron Haussmann redesigned Paris between 1853 and _____.','answer'=>'1870'],
                        ['id'=>'up403','section'=>4,'type'=>'mcq','question'=>'What was the primary motivation for Haussmann\'s redesign of Paris?','options'=>['Aesthetic improvement','Disease prevention and crowd control','Housing the growing population','Creating commercial districts'],'answer'=>'Disease prevention and crowd control'],
                        ['id'=>'up404','section'=>4,'type'=>'fill','question'=>'Ebenezer Howard\'s concept of _____ cities influenced 20th century planning.','answer'=>'garden'],
                        ['id'=>'up405','section'=>4,'type'=>'fill','question'=>'Le Corbusier\'s Plan Voisin proposed demolishing most of central _____.','answer'=>'Paris'],
                        ['id'=>'up406','section'=>4,'type'=>'mcq','question'=>'What does the lecturer identify as the central problem with post-war housing tower blocks?','options'=>['Poor construction quality','Lack of community spaces','High maintenance costs','Inadequate insulation'],'answer'=>'Lack of community spaces'],
                        ['id'=>'up407','section'=>4,'type'=>'fill','question'=>'The _____ movement of the 1980s sought to return to human-scale, walkable neighbourhoods.','answer'=>'New Urbanism'],
                        ['id'=>'up408','section'=>4,'type'=>'fill','question'=>'Smart city technology uses _____ data to improve urban efficiency.','answer'=>'real-time'],
                        ['id'=>'up409','section'=>4,'type'=>'mcq','question'=>'What does the lecturer say is the greatest challenge for 21st century urban planners?','options'=>['Managing population decline','Balancing growth with sustainability','Funding infrastructure','Reducing traffic congestion'],'answer'=>'Balancing growth with sustainability'],
                        ['id'=>'up410','section'=>4,'type'=>'fill','question'=>'By 2050, an estimated _____ percent of the world\'s population will live in cities.','answer'=>'68'],
                    ],
                ],
            ],

            // ── General Set 1: Community Centre & Local Business ──
            [
                'title'    => 'Community Centre Booking & Local Business Panel',
                'category' => 'listening_general',
                'metadata' => [
                    'audio_url' => null,
                    'questions' => [
                        // Section 1 — Phone enquiry about room hire
                        ['id'=>'cc101','section'=>1,'type'=>'fill','question'=>'The main hall can accommodate a maximum of _____ people.','answer'=>'150'],
                        ['id'=>'cc102','section'=>1,'type'=>'fill','question'=>'The hourly rate for the conference room is £_____.','answer'=>'45'],
                        ['id'=>'cc103','section'=>1,'type'=>'mcq','question'=>'What equipment is included in the standard room hire package?','options'=>['Projector only','Projector and microphone','Full AV system and stage','Tables and chairs only'],'answer'=>'Projector and microphone'],
                        ['id'=>'cc104','section'=>1,'type'=>'fill','question'=>'A security deposit of £_____ is required for weekend bookings.','answer'=>'200'],
                        ['id'=>'cc105','section'=>1,'type'=>'fill','question'=>'The catering kitchen is available until _____ pm on weeknights.','answer'=>'10'],
                        ['id'=>'cc106','section'=>1,'type'=>'mcq','question'=>'How far in advance must large events be booked?','options'=>['Two weeks','One month','Six weeks','Three months'],'answer'=>'Six weeks'],
                        ['id'=>'cc107','section'=>1,'type'=>'fill','question'=>'The centre offers a _____ percent discount for registered charities.','answer'=>'20'],
                        ['id'=>'cc108','section'=>1,'type'=>'fill','question'=>'Free parking is available for up to _____ vehicles.','answer'=>'40'],
                        ['id'=>'cc109','section'=>1,'type'=>'mcq','question'=>'What is required to cancel a booking without penalty?','options'=>['24 hours notice','48 hours notice','7 days notice','14 days notice'],'answer'=>'7 days notice'],
                        ['id'=>'cc110','section'=>1,'type'=>'fill','question'=>'The centre manager\'s name is _____.','answer'=>'Patricia Holloway'],

                        // Section 2 — Information talk about local business support scheme
                        ['id'=>'cc201','section'=>2,'type'=>'fill','question'=>'The business support grant offers up to £_____ for start-up costs.','answer'=>'5,000'],
                        ['id'=>'cc202','section'=>2,'type'=>'mcq','question'=>'Who is eligible for the Enterprise Boost programme?','options'=>['All local businesses','Businesses under two years old','Sole traders only','Businesses with fewer than 10 employees'],'answer'=>'Businesses under two years old'],
                        ['id'=>'cc203','section'=>2,'type'=>'fill','question'=>'Free mentoring sessions are available every _____ morning.','answer'=>'Thursday'],
                        ['id'=>'cc204','section'=>2,'type'=>'fill','question'=>'Applications for the grant close on _____ November.','answer'=>'30th'],
                        ['id'=>'cc205','section'=>2,'type'=>'mcq','question'=>'What is the maximum loan available through the Community Enterprise Fund?','options'=>['£10,000','£15,000','£25,000','£50,000'],'answer'=>'£25,000'],
                        ['id'=>'cc206','section'=>2,'type'=>'fill','question'=>'The interest rate on Community Enterprise Fund loans is _____ percent.','answer'=>'3.5'],
                        ['id'=>'cc207','section'=>2,'type'=>'fill','question'=>'Successful applicants must attend a _____ workshop before receiving funds.','answer'=>'financial planning'],
                        ['id'=>'cc208','section'=>2,'type'=>'mcq','question'=>'How many local businesses received support last year?','options'=>['42','67','89','114'],'answer'=>'89'],
                        ['id'=>'cc209','section'=>2,'type'=>'fill','question'=>'The business networking event is held at the _____ Hotel.','answer'=>'Grand'],
                        ['id'=>'cc210','section'=>2,'type'=>'fill','question'=>'The next information session will take place on _____ 15th.','answer'=>'March'],

                        // Section 3 — Discussion between two business owners
                        ['id'=>'cc301','section'=>3,'type'=>'mcq','question'=>'Why did Sarah decide to open a second branch?','options'=>['Customer demand','A favourable lease offer','Her partner\'s suggestion','A reduction in costs'],'answer'=>'A favourable lease offer'],
                        ['id'=>'cc302','section'=>3,'type'=>'fill','question'=>'The second branch will focus on _____ products.','answer'=>'organic'],
                        ['id'=>'cc303','section'=>3,'type'=>'mcq_multi','question'=>'Which TWO challenges do both business owners mention?','options'=>['Finding reliable suppliers','Staff recruitment','Rising rent costs','Online competition','Changing customer preferences'],'answers'=>['Staff recruitment','Rising rent costs']],
                        ['id'=>'cc304','section'=>3,'type'=>'fill','question'=>'Mark attributes 40% of his sales growth to his _____ campaign.','answer'=>'social media'],
                        ['id'=>'cc305','section'=>3,'type'=>'fill','question'=>'Sarah plans to introduce a customer _____ scheme next year.','answer'=>'loyalty'],
                        ['id'=>'cc306','section'=>3,'type'=>'mcq','question'=>'What does Mark recommend as the most important factor in hiring staff?','options'=>['Previous experience','Relevant qualifications','Cultural fit','References'],'answer'=>'Cultural fit'],
                        ['id'=>'cc307','section'=>3,'type'=>'fill','question'=>'Both owners agree that _____ is the key to surviving the first two years.','answer'=>'cash flow management'],
                        ['id'=>'cc308','section'=>3,'type'=>'fill','question'=>'Sarah\'s business has grown by _____ percent in three years.','answer'=>'180'],
                        ['id'=>'cc309','section'=>3,'type'=>'mcq','question'=>'What do the two owners plan to do together?','options'=>['Open a joint venture','Share a marketing campaign','Co-host a community event','Collaborate on a product line'],'answer'=>'Co-host a community event'],
                        ['id'=>'cc310','section'=>3,'type'=>'fill','question'=>'Mark mentions that the best free marketing tool for local businesses is _____.','answer'=>'Google My Business'],

                        // Section 4 — Talk: The Gig Economy
                        ['id'=>'cc401','section'=>4,'type'=>'fill','question'=>'The gig economy is estimated to involve _____ million workers in the UK alone.','answer'=>'4.7'],
                        ['id'=>'cc402','section'=>4,'type'=>'fill','question'=>'The term "gig economy" was first widely used around _____.','answer'=>'2009'],
                        ['id'=>'cc403','section'=>4,'type'=>'mcq','question'=>'What is cited as the primary appeal of gig work for workers?','options'=>['Higher pay','Flexibility','Job security','Career advancement'],'answer'=>'Flexibility'],
                        ['id'=>'cc404','section'=>4,'type'=>'fill','question'=>'Gig workers typically earn _____ percent less than equivalent full-time employees.','answer'=>'30'],
                        ['id'=>'cc405','section'=>4,'type'=>'fill','question'=>'The speaker identifies _____ as the most significant legal challenge facing gig platforms.','answer'=>'worker classification'],
                        ['id'=>'cc406','section'=>4,'type'=>'mcq','question'=>'Which country was the first to introduce legislation specifically protecting gig workers?','options'=>['France','Spain','Australia','Canada'],'answer'=>'Spain'],
                        ['id'=>'cc407','section'=>4,'type'=>'fill','question'=>'Gig workers are _____ times more likely to experience income volatility than full-time employees.','answer'=>'three'],
                        ['id'=>'cc408','section'=>4,'type'=>'fill','question'=>'The speaker argues that the solution requires cooperation between _____ and government.','answer'=>'platforms'],
                        ['id'=>'cc409','section'=>4,'type'=>'mcq','question'=>'What does the speaker predict for the gig economy by 2030?','options'=>['It will decline significantly','It will stabilise at current levels','It will double in size','It will be replaced by AI'],'answer'=>'It will double in size'],
                        ['id'=>'cc410','section'=>4,'type'=>'fill','question'=>'The speaker concludes that portable _____ benefits are the most urgent reform needed.','answer'=>'social security'],
                    ],
                ],
            ],
        ];

        foreach ($sets as $set) {
            Question::updateOrCreate(
                ['title' => $set['title'], 'category' => $set['category']],
                [
                    'type'     => 'listening',
                    'content'  => $set['title'],
                    'metadata' => json_encode($set['metadata']),
                    'active'   => true,
                ]
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // READING — 3 new sets
    // ─────────────────────────────────────────────────────────────────────────
    private function seedReading(): void
    {
        $sets = [

            // ── Academic Reading 2: The Psychology of Decision-Making ──
            [
                'title'    => 'The Psychology of Decision-Making',
                'category' => 'reading_academic',
                'metadata' => [
                    'title'   => 'The Psychology of Decision-Making',
                    'passage' => "Human beings make thousands of decisions every day, from mundane choices such as what to eat for breakfast to life-altering ones concerning career and relationships. For much of the twentieth century, economists and psychologists believed that humans were fundamentally rational actors who weighed up costs and benefits and selected the option that maximised their utility. This model, known as rational choice theory, provided the foundation for classical economics and much of modern decision science.

However, beginning in the 1970s, the work of psychologists Daniel Kahneman and Amos Tversky began to challenge this assumption. Their research demonstrated that human decision-making is riddled with systematic biases and cognitive shortcuts, known as heuristics, that frequently lead to irrational outcomes. Their programme of research, known as Prospect Theory, showed that people do not evaluate outcomes in absolute terms but rather relative to a reference point — typically the status quo. Crucially, losses are felt approximately twice as powerfully as equivalent gains, a phenomenon they called loss aversion.

One of the most well-documented cognitive biases is the anchoring effect. When people are asked to estimate an unknown quantity, their judgement is disproportionately influenced by an initial piece of information — the anchor — even when that information is demonstrably irrelevant. In one famous experiment, participants who were first asked whether the population of Turkey was above or below 5 million gave estimates significantly lower than those first asked whether the figure was above or below 65 million.

The availability heuristic is another well-known shortcut. People judge the probability of events based on how easily relevant examples come to mind. As a result, vivid or recent events — such as plane crashes — are typically overestimated in terms of frequency, while more statistically common but less memorable risks, such as car accidents, are underestimated.

Kahneman later synthesised these findings in his highly influential book, which introduced the concept of two systems of thought. System 1 thinking is fast, automatic, and emotional, operating below conscious awareness and relying on heuristics. System 2 thinking is slow, deliberate, and analytical, requiring conscious effort. Most everyday decisions are governed by System 1, which — while efficient — is prone to systematic errors.

The practical implications of this research have been far-reaching. Behavioural economics — the discipline that applies psychological insights to economic behaviour — has influenced public policy through the concept of nudging. Rather than mandating behaviour, governments can structure the choice environment, or 'choice architecture', in ways that make the desired behaviour the default option. Opt-out organ donation systems, automatic pension enrolment, and the placement of healthy foods at eye level in canteens are all examples of nudges that exploit predictable cognitive biases to improve outcomes without restricting freedom of choice.

Critics, however, argue that nudging is paternalistic, treating citizens as irrational agents who need to be guided towards supposedly better choices by technocratic elites. Others contend that the reproducibility of many behavioural economics findings has been called into question, and that some well-known effects may be considerably smaller or less reliable than originally reported. The debate over the appropriate role of behavioural insights in public policy remains vigorous.",

                    'questions' => [
                        ['id'=>'dm01','type'=>'tfng','question'=>'Rational choice theory assumes that people always make decisions that maximise their personal benefit.','answer'=>'True'],
                        ['id'=>'dm02','type'=>'tfng','question'=>'Kahneman and Tversky published their research in the 1960s.','answer'=>'False'],
                        ['id'=>'dm03','type'=>'tfng','question'=>'According to Prospect Theory, people evaluate outcomes relative to their current situation.','answer'=>'True'],
                        ['id'=>'dm04','type'=>'tfng','question'=>'Loss aversion means that people feel gains and losses with equal intensity.','answer'=>'False'],
                        ['id'=>'dm05','type'=>'tfng','question'=>'The passage states that the anchoring effect only works when the anchor is relevant to the question.','answer'=>'False'],
                        ['id'=>'dm06','type'=>'tfng','question'=>'The availability heuristic causes people to overestimate the frequency of dramatic events.','answer'=>'True'],
                        ['id'=>'dm07','type'=>'tfng','question'=>'System 2 thinking is described as the default mode for most everyday decisions.','answer'=>'False'],
                        ['id'=>'dm08','type'=>'fill','question'=>'The approach of using subtle design choices to influence behaviour without restricting choice is called _____.','answer'=>'nudging'],
                        ['id'=>'dm09','type'=>'fill','question'=>'The idea of structuring the environment in which choices are made is referred to as _____ architecture.','answer'=>'choice'],
                        ['id'=>'dm10','type'=>'fill','question'=>'Opt-out organ donation is an example of making the desired behaviour the _____ option.','answer'=>'default'],
                        ['id'=>'dm11','type'=>'mcq','question'=>'What is the main criticism of nudging mentioned in the final paragraph?','options'=>['It is too expensive to implement','It removes freedom of choice entirely','It treats citizens as unable to make rational decisions','It has been proven ineffective'],'answer'=>'It treats citizens as unable to make rational decisions'],
                        ['id'=>'dm12','type'=>'fill','question'=>'The reproducibility of some behavioural economics findings has been called into _____.','answer'=>'question'],
                        ['id'=>'dm13','type'=>'mcq','question'=>'What does the writer mean by "systematic biases"?','options'=>['Errors that occur randomly and unpredictably','Errors that follow consistent and predictable patterns','Errors caused by a lack of information','Errors unique to individuals'],'answer'=>'Errors that follow consistent and predictable patterns'],
                    ],
                ],
            ],

            // ── Academic Reading 3: The History of Vaccination ──
            [
                'title'    => 'The History and Science of Vaccination',
                'category' => 'reading_academic',
                'metadata' => [
                    'title'   => 'The History and Science of Vaccination',
                    'passage' => "Vaccination is widely regarded as one of the most successful public health interventions in human history. The principle underlying vaccination — that exposure to a weakened or inactivated pathogen can confer protection against future infection — was formalised in the late eighteenth century by Edward Jenner, an English physician who observed that milkmaids who had contracted the relatively mild cowpox disease appeared to be protected against the far more deadly smallpox. In 1796, Jenner inoculated a young boy with material taken from a cowpox sore and subsequently demonstrated that the boy was immune to smallpox. This landmark experiment established the scientific basis for immunisation.

The word 'vaccine' itself derives from the Latin 'vacca', meaning cow, in reference to Jenner's use of cowpox. However, it was the French chemist Louis Pasteur who, nearly a century later, extended and systematised the concept of vaccination through his germ theory of disease and the development of vaccines for cholera in chickens, anthrax, and rabies. Pasteur famously coined the term 'vaccine' in honour of Jenner's pioneering work.

The mechanism by which vaccines confer protection was not fully understood until the development of immunology as a scientific discipline in the twentieth century. Vaccines work by exposing the immune system to antigens — molecules, typically proteins, found on the surface of pathogens — without causing actual disease. This exposure stimulates the immune system to produce antibodies and to create memory cells that persist in the body. When the vaccinated individual subsequently encounters the real pathogen, the immune system can mount a rapid and effective response before the infection can cause serious harm.

There are several types of vaccines. Live-attenuated vaccines use a weakened form of the actual pathogen and typically produce strong, long-lasting immunity. Inactivated vaccines use killed pathogens and generally require booster doses. Subunit vaccines contain only specific proteins from the pathogen rather than the whole organism. More recently, mRNA vaccines — such as those developed against COVID-19 — represent a novel approach in which genetic instructions are delivered to cells, directing them to produce the relevant antigen and thereby trigger an immune response.

The success of vaccination programmes is not solely dependent on the immunological protection they confer on individuals. Herd immunity — the indirect protection afforded to unvaccinated individuals when a sufficiently high proportion of the population is immune — is a crucial concept in public health. When enough people are immune, the pathogen cannot spread efficiently through the population, and even those who cannot be vaccinated (such as newborns or immunocompromised individuals) are protected. The threshold for herd immunity varies by disease; for measles, which is highly contagious, approximately 95% of the population must be immune to prevent sustained transmission.

Despite the overwhelming evidence of their safety and efficacy, vaccines have faced persistent opposition throughout their history. In the nineteenth century, compulsory smallpox vaccination in Britain was met with organised resistance on the grounds of personal liberty. In the late twentieth century, a now-discredited study falsely linking the MMR vaccine to autism caused significant disruption to vaccination programmes in several countries, the effects of which are still felt today in reduced uptake rates. Public health authorities continue to grapple with the challenge of communicating accurate information in an era of widespread misinformation.",

                    'questions' => [
                        ['id'=>'vx01','type'=>'yngng','question'=>'Edward Jenner conducted his first vaccination experiment in the seventeenth century.','answer'=>'No'],
                        ['id'=>'vx02','type'=>'yngng','question'=>'Jenner\'s work was based on observations he made about milkmaids.','answer'=>'Yes'],
                        ['id'=>'vx03','type'=>'yngng','question'=>'The word "vaccine" was coined by Edward Jenner himself.','answer'=>'No'],
                        ['id'=>'vx04','type'=>'yngng','question'=>'Louis Pasteur developed vaccines for more than one disease.','answer'=>'Yes'],
                        ['id'=>'vx05','type'=>'yngng','question'=>'The passage states that the full mechanism of vaccines was understood in Jenner\'s lifetime.','answer'=>'No'],
                        ['id'=>'vx06','type'=>'fill','question'=>'The molecules that vaccines use to stimulate an immune response are called _____.','answer'=>'antigens'],
                        ['id'=>'vx07','type'=>'fill','question'=>'Vaccines work by causing the immune system to produce antibodies and _____ cells.','answer'=>'memory'],
                        ['id'=>'vx08','type'=>'mcq','question'=>'Which type of vaccine typically requires booster doses according to the passage?','options'=>['Live-attenuated vaccines','Inactivated vaccines','Subunit vaccines','mRNA vaccines'],'answer'=>'Inactivated vaccines'],
                        ['id'=>'vx09','type'=>'fill','question'=>'mRNA vaccines work by delivering _____ instructions to cells.','answer'=>'genetic'],
                        ['id'=>'vx10','type'=>'fill','question'=>'The indirect protection given to unvaccinated individuals by a highly immune population is called _____ immunity.','answer'=>'herd'],
                        ['id'=>'vx11','type'=>'fill','question'=>'For measles, approximately _____ percent of the population must be immune for herd immunity to work.','answer'=>'95'],
                        ['id'=>'vx12','type'=>'mcq','question'=>'What was the main ground for opposition to compulsory smallpox vaccination in nineteenth-century Britain?','options'=>['Concerns about side effects','Religious objections','Personal liberty','Distrust of the government'],'answer'=>'Personal liberty'],
                        ['id'=>'vx13','type'=>'fill','question'=>'The study linking MMR to autism has since been described as _____.','answer'=>'discredited'],
                    ],
                ],
            ],

            // ── General Reading 2: Working From Home ──
            [
                'title'    => 'The Rise of Remote Working',
                'category' => 'reading_general',
                'metadata' => [
                    'title'   => 'The Rise of Remote Working',
                    'passage' => "The concept of working from home is not new — tradespeople and artisans have operated from domestic premises for centuries — but the mass adoption of remote working by office-based employees is a phenomenon of the twenty-first century, dramatically accelerated by the global pandemic of 2020.

Prior to 2020, surveys suggested that only around 5% of workers in most developed economies worked primarily from home. Within weeks of the first lockdowns, this figure jumped to over 40% in many countries as businesses scrambled to maintain operations without physical premises. Technology companies and financial services firms led the way, rapidly deploying cloud-based collaboration tools, video-conferencing platforms, and virtual private networks to enable their workforces to operate remotely.

The evidence on productivity is more nuanced than the early optimism suggested. Some studies conducted during the pandemic period showed productivity gains, particularly for tasks requiring deep concentration, as workers were freed from the interruptions and commuting burdens of the traditional office. However, other research pointed to declines in collaborative creativity, mentoring of junior staff, and the informal knowledge transfer that happens organically in shared workspaces. Stanford economist Nicholas Bloom, who has studied remote work extensively, has concluded that a hybrid model — typically two to three days in the office per week — may represent the optimal balance.

For employees, the benefits of remote work extend beyond the obvious convenience of avoiding the daily commute. Workers report greater autonomy, improved work-life balance, and, in many cases, significant savings on transport, work clothing, and lunches. For those with caring responsibilities or chronic health conditions, remote work has opened doors to employment that were previously closed. However, not all employees have thrived. Those in small or noisy homes, those living alone, and younger workers who miss the social interaction and developmental opportunities of office life have often struggled.

Employers, too, have had mixed experiences. Office space costs have fallen as leases are renegotiated or surrendered, and the ability to recruit from a wider geographical pool has broadened access to talent. Some companies have used the shift to fully remote working as an opportunity to become genuinely global organisations. However, maintaining company culture, ensuring data security, and managing performance across distributed teams have proven significant challenges.

The long-term picture remains uncertain. A number of high-profile technology companies have reversed their remote-work policies, mandating a full return to the office on the grounds that in-person collaboration drives innovation. Others have committed to permanent hybrid arrangements. Governments, meanwhile, are beginning to consider the broader implications: the depopulation of city centres, the impact on commercial real estate, and the potential for remote work to revitalise smaller towns and rural areas as workers relocate away from expensive urban centres.

What seems clear is that the traditional nine-to-five, five-days-a-week office model will never fully return. The pandemic fundamentally altered expectations on both sides of the employment relationship, and the future of work will be defined by negotiation, flexibility, and the ongoing tension between individual preference and organisational need.",

                    'questions' => [
                        ['id'=>'rw01','type'=>'tfng','question'=>'Working from home was entirely unknown before the twentieth century.','answer'=>'False'],
                        ['id'=>'rw02','type'=>'tfng','question'=>'Before 2020, more than 10% of workers in most developed economies worked primarily from home.','answer'=>'False'],
                        ['id'=>'rw03','type'=>'tfng','question'=>'Remote work increased productivity for all types of tasks during the pandemic.','answer'=>'False'],
                        ['id'=>'rw04','type'=>'tfng','question'=>'According to researcher Nicholas Bloom, working in the office every day is the most productive arrangement.','answer'=>'False'],
                        ['id'=>'rw05','type'=>'tfng','question'=>'Some workers with health conditions have benefited from the rise of remote work.','answer'=>'True'],
                        ['id'=>'rw06','type'=>'tfng','question'=>'All employees found remote working a positive experience during the pandemic.','answer'=>'False'],
                        ['id'=>'rw07','type'=>'fill','question'=>'Companies that went fully remote were able to recruit from a wider _____ pool.','answer'=>'geographical'],
                        ['id'=>'rw08','type'=>'fill','question'=>'Maintaining company _____ was identified as one of the biggest challenges for employers.','answer'=>'culture'],
                        ['id'=>'rw09','type'=>'mcq','question'=>'Why have some technology companies reversed their remote-work policies?','options'=>['To reduce operational costs','Because employees requested it','Because they believe in-person work drives innovation','Due to government mandates'],'answer'=>'Because they believe in-person work drives innovation'],
                        ['id'=>'rw10','type'=>'fill','question'=>'Governments are concerned about the possible _____ of city centres as more people work remotely.','answer'=>'depopulation'],
                        ['id'=>'rw11','type'=>'mcq','question'=>'Which group of employees does the passage suggest struggled most with remote working?','options'=>['Senior managers','Workers with caring responsibilities','Young workers in small homes','Financial services employees'],'answer'=>'Young workers in small homes'],
                        ['id'=>'rw12','type'=>'fill','question'=>'The writer concludes that the traditional five-days-a-week office model will never _____ return.','answer'=>'fully'],
                        ['id'=>'rw13','type'=>'mcq','question'=>'What does the writer say will define the future of work?','options'=>['Government legislation','Technology capability','Flexibility and negotiation','The preferences of employers'],'answer'=>'Flexibility and negotiation'],
                    ],
                ],
            ],
        ];

        foreach ($sets as $set) {
            Question::updateOrCreate(
                ['title' => $set['title'], 'category' => $set['category']],
                [
                    'type'     => 'reading',
                    'content'  => $set['title'],
                    'metadata' => json_encode($set['metadata']),
                    'active'   => true,
                ]
            );
        }
    }
}
