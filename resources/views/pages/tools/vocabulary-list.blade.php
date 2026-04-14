<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IELTS Vocabulary List — 80 High-Frequency Academic Words | IELTS Band AI</title>
    <meta name="description" content="Master 80 essential IELTS vocabulary words organised by topic. Each entry includes the word, part of speech, definition, and an example sentence for exam context.">
    <meta name="keywords" content="IELTS vocabulary list, IELTS academic vocabulary, IELTS word list, IELTS vocabulary for writing, high frequency IELTS words, IELTS vocabulary 2024">
    <meta property="og:title" content="IELTS Vocabulary List — 80 High-Frequency Academic Words">
    <meta property="og:description" content="80 essential IELTS vocabulary words by topic — with definitions and example sentences.">
    <meta property="og:type" content="website">
    <link rel="canonical" href="{{ url('/ielts-vocabulary-list') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-950 text-surface-100 min-h-screen">

{{-- Minimal nav --}}
<nav class="bg-surface-900 border-b border-surface-700 px-4 py-3 flex items-center justify-between">
    <a href="{{ url('/') }}" class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/></svg>
        </div>
        <span class="font-bold text-surface-50">IELTS Band AI</span>
    </a>
    <a href="{{ route('register') }}" class="btn-primary text-sm px-4 py-2">Free Sign Up</a>
</nav>

@php
$vocab = [
    'Environment' => [
        ['deteriorate',   'verb',       'To become progressively worse',                        'Air quality in many major cities continues to deteriorate due to industrial emissions.'],
        ['biodiversity',  'noun',       'The variety of plant and animal life in an ecosystem',  'Deforestation threatens the rich biodiversity of tropical rainforests.'],
        ['sustainable',   'adjective',  'Able to be maintained long-term without damaging resources', 'Governments must invest in sustainable energy sources to combat climate change.'],
        ['mitigate',      'verb',       'To lessen the severity of something',                  'Planting urban trees can mitigate the effects of rising city temperatures.'],
        ['ecological',    'adjective',  'Relating to the relationships between organisms and their environment', 'The factory\'s discharge caused significant ecological damage to the river.'],
        ['renewable',     'adjective',  'Naturally replenished and not depleted by use',         'Solar and wind power are among the most accessible renewable energy sources.'],
        ['deforestation', 'noun',       'The large-scale clearing of forest land',              'Deforestation in the Amazon Basin has accelerated at an alarming rate.'],
        ['emissions',     'noun',       'Gases or other substances released into the atmosphere','Carbon emissions from transport account for a large share of global warming.'],
        ['conservation',  'noun',       'The protection and careful management of natural resources', 'Marine conservation efforts have helped some whale populations recover.'],
        ['contaminate',   'verb',       'To make impure or dangerous by introducing pollutants','Industrial waste can contaminate groundwater supplies for decades.'],
        ['carbon footprint', 'noun',    'The total greenhouse gases produced by an individual or activity', 'Flying long distances significantly increases a person\'s carbon footprint.'],
        ['ecosystem',     'noun',       'A biological community of interacting organisms and their environment', 'Wetlands form a vital ecosystem that supports thousands of species.'],
        ['fossil fuels',  'noun',       'Coal, oil, and gas formed from ancient organic material','Our dependence on fossil fuels is the primary driver of climate change.'],
        ['reforestation', 'noun',       'The replanting of trees in deforested areas',          'Large-scale reforestation projects can restore habitats and absorb CO₂.'],
        ['habitat',       'noun',       'The natural environment in which an organism lives',   'Urban expansion continues to destroy the natural habitat of many species.'],
        ['overfishing',   'noun',       'Depleting fish stocks by catching too many fish',      'Overfishing has pushed several commercially important species to the brink of extinction.'],
    ],
    'Technology' => [
        ['innovation',    'noun',       'A new method, idea, or product',                       'Technological innovation has transformed the way people communicate globally.'],
        ['automation',    'noun',       'The use of machines or software to perform tasks without human input', 'Automation in manufacturing has displaced many low-skilled workers.'],
        ['algorithm',     'noun',       'A set of rules or instructions followed by a computer', 'Social media platforms use algorithms to determine what content users see.'],
        ['surveillance',  'noun',       'Close monitoring of people, often using technology',   'The expansion of surveillance cameras raises serious concerns about privacy.'],
        ['proliferation', 'noun',       'Rapid increase or spread',                             'The proliferation of smartphones has fundamentally altered social behaviour.'],
        ['artificial intelligence', 'noun', 'Computer systems that simulate human intelligence', 'Artificial intelligence is increasingly used in medical diagnostics.'],
        ['cybersecurity', 'noun',       'The protection of computer systems from digital attacks','Governments are investing heavily in cybersecurity to protect critical infrastructure.'],
        ['obsolete',      'adjective',  'No longer in use; out of date',                        'Printed encyclopaedias became largely obsolete with the rise of the internet.'],
        ['digital divide', 'noun',      'The gap between those with and without internet access','The digital divide risks widening inequality between developed and developing nations.'],
        ['disruptive',    'adjective',  'Causing fundamental change to an existing industry or system', 'Ride-sharing apps have been disruptive to the traditional taxi industry.'],
        ['data privacy',  'noun',       'The right of individuals to control their personal information', 'Growing concerns about data privacy have prompted calls for stricter regulation.'],
        ['connectivity',  'noun',       'The state of being connected to a network',             'High-speed internet connectivity is now considered essential infrastructure.'],
        ['virtual reality','noun',      'A computer-generated simulation of a 3D environment',  'Virtual reality is being used to train surgeons in complex procedures.'],
        ['renewable technology','noun', 'Technology that generates energy from natural sources', 'Investment in renewable technology has grown dramatically over the past decade.'],
        ['biotechnology', 'noun',       'The use of biology to develop products and processes',  'Advances in biotechnology have led to new treatments for genetic diseases.'],
        ['platform',      'noun',       'A digital service that enables users to interact and transact', 'Online platforms have created entirely new business models and industries.'],
    ],
    'Health' => [
        ['pandemic',      'noun',       'An epidemic of disease that has spread across a large region', 'The 2020 pandemic highlighted the importance of international health cooperation.'],
        ['obesity',       'noun',       'The condition of being severely overweight',            'Rising rates of obesity are placing enormous strain on healthcare systems.'],
        ['mental health', 'noun',       'A person\'s psychological and emotional well-being',   'Governments must invest more resources in mental health services.'],
        ['sedentary',     'adjective',  'Characterised by little physical activity',             'A sedentary lifestyle increases the risk of cardiovascular disease.'],
        ['preventative',  'adjective',  'Designed to stop something from happening',             'Preventative medicine, including vaccination, is more cost-effective than treatment.'],
        ['malnutrition',  'noun',       'Lack of proper nutrition',                              'Malnutrition in early childhood can cause lasting developmental damage.'],
        ['mortality rate','noun',       'The proportion of deaths in a given population',       'Improved sanitation has significantly reduced the infant mortality rate.'],
        ['chronic',       'adjective',  'Persisting for a long time',                           'Diabetes is one of the most common chronic diseases worldwide.'],
        ['epidemic',      'noun',       'A widespread occurrence of a disease in a community',  'Smoking-related illnesses remain a major epidemic in many low-income countries.'],
        ['vaccination',   'noun',       'The introduction of a vaccine to provide immunity',    'Mass vaccination programmes have eradicated smallpox globally.'],
        ['psychological', 'adjective',  'Relating to the mind and mental processes',            'Long working hours can have serious psychological effects on employees.'],
        ['antibiotic resistance','noun','When bacteria evolve to resist the drugs that kill them','Antibiotic resistance poses one of the greatest threats to modern medicine.'],
        ['life expectancy','noun',      'The average period a person is expected to live',      'Advances in medicine have significantly increased life expectancy in wealthy nations.'],
        ['holistic',      'adjective',  'Treating the whole person rather than just symptoms',  'A holistic approach to healthcare considers physical, mental, and social well-being.'],
        ['epidemic',      'noun',       'A widespread occurrence of disease in a community',    'The obesity epidemic is driven by both lifestyle changes and food industry practices.'],
        ['healthcare disparity','noun', 'Unequal access to healthcare across different groups', 'Healthcare disparity between urban and rural populations remains a pressing problem.'],
    ],
    'Education' => [
        ['literacy',      'noun',       'The ability to read and write',                        'Improving adult literacy rates is fundamental to economic development.'],
        ['curriculum',    'noun',       'The subjects and content taught at a school',          'Many educators argue the school curriculum should include financial literacy.'],
        ['pedagogy',      'noun',       'The method and practice of teaching',                  'Progressive pedagogy emphasises student-led inquiry over passive learning.'],
        ['critical thinking','noun',    'The ability to analyse information objectively',       'Employers consistently cite critical thinking as one of the most valued graduate skills.'],
        ['academic achievement','noun', 'Success in formal educational qualifications',         'Socioeconomic background remains a strong predictor of academic achievement.'],
        ['vocational',    'adjective',  'Relating to practical skills for a specific trade or occupation', 'Vocational training provides an essential alternative to university education.'],
        ['equity',        'noun',       'Fairness and justice in access to resources',          'Educational equity requires addressing disparities between well-funded and underfunded schools.'],
        ['standardised testing','noun', 'Uniform tests given to all students under the same conditions', 'Critics argue that standardised testing narrows the curriculum.'],
        ['dropout rate',  'noun',       'The proportion of students who leave education before completing it', 'High school dropout rates are closely linked to poverty.'],
        ['higher education','noun',     'Education beyond secondary school, especially at university','The cost of higher education has increased dramatically in many countries.'],
        ['rote learning', 'noun',       'Learning through memorisation rather than understanding','Rote learning discourages curiosity and independent thought.'],
        ['inclusive education','noun',  'An approach that supports all students regardless of ability', 'Inclusive education requires both funding and teacher training to succeed.'],
        ['extracurricular','adjective', 'Relating to activities outside the formal curriculum', 'Extracurricular activities develop leadership and teamwork skills in students.'],
        ['lifelong learning','noun',    'Ongoing, voluntary self-improvement through education', 'Rapid technological change makes lifelong learning a professional necessity.'],
        ['scholarship',   'noun',       'A grant of financial aid for education',               'Merit-based scholarships help talented students from disadvantaged backgrounds access university.'],
        ['tuition fees',  'noun',       'The charge for instruction at an educational institution','Rising tuition fees have left many graduates with significant debt.'],
    ],
    'Society' => [
        ['inequality',    'noun',       'The unequal distribution of resources or opportunities','Rising income inequality undermines social cohesion and democratic participation.'],
        ['immigration',   'noun',       'The movement of people into a country to live permanently', 'Immigration has historically been a significant driver of economic growth.'],
        ['urbanisation',  'noun',       'The process by which rural areas become urban',        'Rapid urbanisation has placed enormous pressure on city infrastructure.'],
        ['discrimination','noun',       'Unjust treatment of different groups of people',       'Workplace discrimination on the basis of gender remains a widespread problem.'],
        ['social mobility','noun',      'The ability to move between social classes',           'Countries with strong public education systems tend to have higher social mobility.'],
        ['welfare state', 'noun',       'A system in which the government provides for citizens\' needs', 'The welfare state has reduced poverty but is increasingly expensive to maintain.'],
        ['globalisation', 'noun',       'The process of increased global interconnection',      'Globalisation has boosted trade but also contributed to cultural homogenisation.'],
        ['demographic',   'adjective',  'Relating to the structure and characteristics of a population', 'Ageing demographic trends present long-term challenges for pension systems.'],
        ['consumerism',   'noun',       'The preoccupation with buying goods and services',     'Critics argue that consumerism encourages excessive waste and environmental damage.'],
        ['philanthropy',  'noun',       'Charitable giving for the benefit of society',         'Philanthropy plays an increasingly significant role in funding public services.'],
        ['marginalised',  'adjective',  'Treated as insignificant or excluded from mainstream society', 'Marginalised communities often have less access to legal and health services.'],
        ['civil liberties','noun',      'Fundamental rights protected by law',                  'The expansion of surveillance technology has reignited debates over civil liberties.'],
        ['cohesion',      'noun',       'The action of forming a united whole',                 'Social cohesion is strengthened when diverse communities share common public spaces.'],
        ['austerity',     'noun',       'Government policies to reduce spending',               'Prolonged austerity measures have eroded public services in many European nations.'],
        ['refugee',       'noun',       'A person forced to flee their country due to war or persecution', 'The global refugee crisis demands a coordinated international response.'],
        ['corruption',    'noun',       'Dishonest or illegal behaviour by those in power',     'Endemic corruption deters foreign investment and weakens institutional trust.'],
    ],
];

$categoryColors = [
    'Environment' => ['bg' => 'bg-emerald-500/15', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/30'],
    'Technology'  => ['bg' => 'bg-brand-500/15',   'text' => 'text-brand-400',   'border' => 'border-brand-500/30'],
    'Health'      => ['bg' => 'bg-rose-500/15',     'text' => 'text-rose-400',    'border' => 'border-rose-500/30'],
    'Education'   => ['bg' => 'bg-amber-500/15',    'text' => 'text-amber-400',   'border' => 'border-amber-500/30'],
    'Society'     => ['bg' => 'bg-purple-500/15',   'text' => 'text-purple-400',  'border' => 'border-purple-500/30'],
];
@endphp

<main class="max-w-5xl mx-auto px-4 py-12" x-data="vocabFilter()">

    <div class="text-center mb-10">
        <h1 class="text-3xl sm:text-4xl font-bold text-surface-50 mb-3">IELTS Vocabulary List</h1>
        <p class="text-surface-400 max-w-2xl mx-auto">80 high-frequency academic words organised by topic — with definitions and real IELTS exam example sentences.</p>
    </div>

    {{-- Category filter --}}
    <div class="flex flex-wrap gap-2 justify-center mb-10">
        <button @click="active = 'all'"
            :class="active === 'all' ? 'bg-brand-500 text-white border-brand-500' : 'bg-surface-800 text-surface-400 border-surface-700 hover:border-brand-500/50 hover:text-surface-200'"
            class="px-4 py-2 rounded-full text-sm font-semibold border transition-all">
            All (80)
        </button>
        @foreach(array_keys($vocab) as $cat)
        <button @click="active = '{{ $cat }}'"
            :class="active === '{{ $cat }}' ? '{{ $categoryColors[$cat]['bg'] }} {{ $categoryColors[$cat]['text'] }} {{ $categoryColors[$cat]['border'] }}' : 'bg-surface-800 text-surface-400 border-surface-700 hover:border-surface-500 hover:text-surface-200'"
            class="px-4 py-2 rounded-full text-sm font-semibold border transition-all">
            {{ $cat }} ({{ count($vocab[$cat]) }})
        </button>
        @endforeach
    </div>

    {{-- Word count indicator --}}
    <p class="text-center text-xs text-surface-500 mb-6">
        Showing <span x-text="count()" class="text-surface-300 font-semibold"></span> words
    </p>

    {{-- Vocabulary sections --}}
    @foreach($vocab as $category => $words)
    <section x-show="active === 'all' || active === '{{ $category }}'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mb-12">

        <div class="flex items-center gap-3 mb-5">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $categoryColors[$category]['bg'] }} {{ $categoryColors[$category]['text'] }} border {{ $categoryColors[$category]['border'] }}">
                {{ $category }}
            </span>
            <div class="flex-1 h-px bg-surface-700"></div>
            <span class="text-xs text-surface-600">{{ count($words) }} words</span>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-2 gap-3">
            @foreach($words as [$word, $pos, $def, $example])
            <div class="card p-4 hover:border-surface-600 transition-colors">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <span class="font-bold text-surface-50 text-base">{{ $word }}</span>
                    <span class="text-xs {{ $categoryColors[$category]['text'] }} font-medium shrink-0 italic">{{ $pos }}</span>
                </div>
                <p class="text-sm text-surface-300 mb-2">{{ $def }}</p>
                <p class="text-xs text-surface-500 border-t border-surface-700 pt-2 italic leading-relaxed">"{{ $example }}"</p>
            </div>
            @endforeach
        </div>
    </section>
    @endforeach

    {{-- SEO content --}}
    <div class="mt-8 space-y-4 text-surface-400 text-sm leading-relaxed">
        <h2 class="text-xl font-semibold text-surface-200">Why vocabulary matters so much in IELTS</h2>
        <p>Lexical Resource — the IELTS term for vocabulary — makes up 25% of your Writing and Speaking band score. To score Band 7 or above, examiners expect you to use a wide range of vocabulary with flexibility and precision. This means choosing words that are appropriate to the context, using collocations naturally, and avoiding repetition of basic words. Learning vocabulary in topic-based sets, as shown above, is the most efficient approach because IELTS essays consistently return to the same five or six subject areas.</p>
        <h2 class="text-xl font-semibold text-surface-200 mt-6">How to learn IELTS vocabulary effectively</h2>
        <p>Research consistently shows that vocabulary is best learned in context. Rather than memorising isolated word lists, study each word in a full sentence, and then try to write your own example. For every new word you learn, also learn its common collocations — for instance, you "raise awareness" but "tackle a problem." Using new vocabulary in your IELTS practice essays, and then getting AI feedback on whether you have used it naturally, is the single most effective way to build active vocabulary.</p>
    </div>

    {{-- CTA --}}
    <div class="mt-12 card p-8 text-center border-glow">
        <h2 class="text-2xl font-bold text-surface-50 mb-3">Use These Words in a Scored Essay</h2>
        <p class="text-surface-400 mb-6 max-w-lg mx-auto">Write an IELTS essay using your new vocabulary and get instant AI feedback showing whether you have used each word naturally and precisely.</p>
        <a href="{{ route('register') }}" class="btn-primary px-8 py-3 text-base font-semibold">Start Free Now</a>
    </div>

</main>

<script>
function vocabFilter() {
    return {
        active: 'all',
        count() {
            const totals = { all: 80, Environment: 16, Technology: 16, Health: 16, Education: 16, Society: 16 };
            return totals[this.active] ?? 0;
        }
    };
}
</script>

</body>
</html>
