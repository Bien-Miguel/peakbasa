<?php
// =========================================
// FILIPINO LANGUAGE LEARNING QUIZ DATA (60 QUESTIONS)
// =========================================
// Format rules:
// - 'truefalse' answers: 'true' or 'false'
// - 'mcq' answers: lowercase string of correct option
// - 'fillblank' answers: lowercase string of missing word/phrase

$quiz_data = [
    // --- LEVEL 1: Mga Pangunahin (Basics) ---
    1 => [
        // QUIZ 1: True / False
        1 => [
            'title' => 'Basic Vocabulary (True or False)',
            'type' => 'truefalse',
            'questions' => [
                ['q' => "The word 'maganda' means 'beautiful' in English.", 'answer' => 'true', 'stars' => 1],
                ['q' => "The phrase 'Salamat' means 'Goodbye'.", 'answer' => 'false', 'stars' => 1], // It means 'Thank you'
                ['q' => "'Malaki' and 'maliit' are antonyms (opposites).", 'answer' => 'true', 'stars' => 1], // Big and small
                ['q' => "The word 'aso' refers to a cat.", 'answer' => 'false', 'stars' => 1], // It means 'dog'
                ['q' => "'Oo' is the Filipino word for 'No'.", 'answer' => 'false', 'stars' => 2], // It means 'Yes'
            ]
        ],
        // QUIZ 2: Multiple Choice
        2 => [
            'title' => 'Common Phrases',
            'type' => 'mcq',
            'questions' => [
                ['q' => "What does 'Kumusta ka?' mean in English?", 'options' => ["Good morning", "How are you?", "Thank you", "What is your name?"], 'answer' => 'how are you?', 'stars' => 2],
                ['q' => "Which word means 'happy' in Filipino?", 'options' => ["Malungkot", "Masaya", "Galit", "Pagod"], 'answer' => 'masaya', 'stars' => 2],
                ['q' => "What is the Filipino word for 'water'?", 'options' => ["Tubig", "Kape", "Gatas", "Bigas"], 'answer' => 'tubig', 'stars' => 1],
                ['q' => "'Magandang gabi' translates to:", 'options' => ["Good night", "Good afternoon", "Good morning", "Good evening"], 'answer' => 'good evening', 'stars' => 2],
                ['q' => "What does 'Saan ka pupunta?' ask?", 'options' => ["What are you eating?", "Who are you with?", "Where are you going?", "When are you leaving?"], 'answer' => 'where are you going?', 'stars' => 2],
            ]
        ],
        // QUIZ 3: Fill in the Blanks
        3 => [
            'title' => 'Complete the Phrase',
            'type' => 'fillblank',
            'questions' => [
                ['q' => "The Filipino word for 'house' is _____.", 'answer' => 'bahay', 'stars' => 2],
                ['q' => "'Thank you' in Filipino is _____.", 'answer' => 'salamat', 'stars' => 1],
                ['q' => "The word for 'teacher' in Filipino is _____.", 'answer' => 'guro', 'stars' => 2],
                ['q' => "'I love you' translates to 'Mahal _____'.", 'answer' => 'kita', 'stars' => 3],
                ['q' => "To say 'Goodbye', you can say '_____'.", 'answer' => 'paalam', 'stars' => 2],
            ]
        ],
    ],

    // --- LEVEL 2: Pagbuo ng Pangungusap (Sentence Building) ---    
    2 => [
        // QUIZ 1: True / False
        1 => [
            'title' => 'Simple Sentences (True or False)',
            'type' => 'truefalse',
            'questions' => [
                ['q' => "The sentence 'Kumakain ako ng tinapay.' means 'I am eating bread.'", 'answer' => 'true', 'stars' => 3],
                ['q' => "'Masaya ang mga bata.' translates to 'The cat is happy.'", 'answer' => 'false', 'stars' => 3], // It means 'The children are happy.'
                ['q' => "In Filipino, sentences can often start with the verb (e.g., 'Tumakbo ang aso.')", 'answer' => 'true', 'stars' => 4],
                ['q' => "'Gusto ko ng kape.' means 'I want coffee.'", 'answer' => 'true', 'stars' => 3],
                ['q' => "'Si' and 'Sina' are markers used for names of people.", 'answer' => 'true', 'stars' => 4], // Si Juan, Sina Juan at Maria
            ]
        ],
        // QUIZ 2: Multiple Choice
        2 => [
            'title' => 'Pronouns and Markers',
            'type' => 'mcq',
            'questions' => [
                ['q' => "How do you say 'The dog is big' in Filipino?", 'options' => ["Malaki ang aso.", "Malaki ng aso.", "Malaki sa aso.", "Malaki si aso."], 'answer' => 'malaki ang aso.', 'stars' => 4],
                ['q' => "What does 'Nagluluto si Nanay' mean?", 'options' => ["Mom is sleeping.", "Mom is cooking.", "Dad is cooking.", "Mom is eating."], 'answer' => 'mom is cooking.', 'stars' => 4],
                ['q' => "Which is the correct pronoun for 'they'?", 'options' => ["Ako", "Ikaw", "Siya", "Sila"], 'answer' => 'sila', 'stars' => 3],
                ['q' => "Which marker is for a location? (e.g., 'Pumunta _____ simbahan.')", 'options' => ["ang", "ng", "sa", "si"], 'answer' => 'sa', 'stars' => 4],
                ['q' => "What is the *verb* in the sentence 'Bumili ng isda si Maria'?", 'options' => ["Bumili", "Isda", "Si", "Maria"], 'answer' => 'bumili', 'stars' => 4],
            ]
        ],
        // QUIZ 3: Fill in the Blanks
        3 => [
            'title' => 'Complete the Sentence',
            'type' => 'fillblank',
            'questions' => [
                ['q' => "'Gusto _____ matulog.' (I want to sleep.)", 'answer' => 'ko', 'stars' => 4],
                ['q' => "'Nasaan ang _____ mo?' (Where is your key?)", 'answer' => 'susi', 'stars' => 3],
                ['q' => "'Umuulan nang _____' (It is raining hard.)", 'answer' => 'malakas', 'stars' => 4],
                ['q' => "'Ito ay _____ pusa.' (This is my cat.)", 'answer' => 'aking', 'stars' => 4], // 'kong' is also valid but 'aking' is clearer
                ['q' => "'Magbabasa _____ ng libro.' (He/She will read a book.)", 'answer' => 'siya', 'stars' => 3],
            ]
        ],
    ],

    // --- LEVEL 3: Gramatika (Grammar) - Tenses & Conjunctions ---
    3 => [
        // QUIZ 1: True / False
        1 => [
            'title' => 'Verb Tenses (True or False)',
            'type' => 'truefalse',
            'questions' => [
                ['q' => "The word 'kumain' (ate) is in the *present* tense.", 'answer' => 'false', 'stars' => 5], // It's past tense
                ['q' => "'Tumatakbo' (running) is in the *present* tense.", 'answer' => 'true', 'stars' => 5],
                ['q' => "The future tense of 'lakad' (walk) is 'lalakad' (will walk).", 'answer' => 'true', 'stars' => 5],
                ['q' => "'Ng' (pronounced 'nang') is often used to mark the object of a verb (e.g., 'Bumili ako NG isda.')", 'answer' => 'true', 'stars' => 6],
                ['q' => "The word 'pero' means 'and' in English.", 'answer' => 'false', 'stars' => 5], // It means 'but'
            ]
        ],
        // QUIZ 2: Multiple Choice
        2 => [
            'title' => 'Choosing the Right Tense',
            'type' => 'mcq',
            'questions' => [
                ['q' => "What is the PAST tense of 'kain' (eat)?", 'options' => ["Kumakain", "Kakain", "Kumain", "Nakain"], 'answer' => 'kumain', 'stars' => 5],
                ['q' => "What is the PRESENT tense of 'sulat' (write)?", 'options' => ["Sumulat", "Susulat", "Sinulat", "Sumusulat"], 'answer' => 'sumusulat', 'stars' => 5],
                ['q' => "What is the FUTURE tense of 'ligo' (bathe)?", 'options' => ["Naligo", "Naliligo", "Maliligo", "Ligo"], 'answer' => 'maliligo', 'stars' => 5],
                ['q' => "Which conjunction fits? 'Nag-aral siya, _____ mataas ang nakuha niya.' (He studied, ___ he got a high grade.)", 'options' => ["pero (but)", "kaya (so)", "o (or)", "dahil (because)"], 'answer' => 'kaya (so)', 'stars' => 6],
                ['q' => "Which conjunction fits? 'Gusto ko, _____ ayaw niya.' (I want to, ___ he/she doesn't.)", 'options' => ["at (and)", "habang (while)", "pero (but)", "para (for)"], 'answer' => 'pero (but)', 'stars' => 6],
            ]
        ],
        // QUIZ 3: Fill in the Blanks
        3 => [
            'title' => 'Complete with Correct Tense/Word',
            'type' => 'fillblank',
            'questions' => [
                ['q' => "(Past) '_____ kami sa mall kahapon.' (We went to the mall yesterday.)", 'answer' => 'pumunta', 'stars' => 6],
                ['q' => "(Present) '_____ siya ng TV ngayon.' (He/She is watching TV right now.)", 'answer' => 'nanonood', 'stars' => 6],
                ['q' => "(Future) '_____ ako bukas.' (I will sing tomorrow.)", 'answer' => 'kakanta', 'stars' => 6],
                ['q' => "'Ibigay mo ito _____ Maria.' (Give this to Maria.)", 'answer' => 'kay', 'stars' => 6], // 'kay' for people, 'sa' for objects/places
                ['q' => "'Mas matangkad si Juan _____ Pedro.' (Juan is taller than Pedro.)", 'answer' => 'kaysa kay', 'stars' => 7], // 'kaysa kay' for comparing people
            ]
        ],
    ],

    // --- LEVEL 4: Mas Malalim (Deeper Concepts) ---
    4 => [
        // QUIZ 1: True / False
        1 => [
            'title' => 'Grammar Focus (True or False)',
            'type' => 'truefalse',
            'questions' => [
                ['q' => "In 'Kinain ng pusa ang isda' (The fish was eaten by the cat), the focus is on 'ang isda' (the fish).", 'answer' => 'true', 'stars' => 8], // Object-focus
                ['q' => "In 'Bumili ang bata ng laruan' (The child bought a toy), the focus is on 'ang bata' (the child).", 'answer' => 'true', 'stars' => 8], // Actor-focus
                ['q' => "The word 'sana' is used to express hope or a wish (e.g., 'Sana manalo ako.')", 'answer' => 'true', 'stars' => 7],
                ['q' => "'Daw' and 'Raw' mean the same thing ('reportedly' / 'they said') and can be used interchangeably.", 'answer' => 'false', 'stars' => 8], // 'raw' is used after vowels, 'daw' after consonants
                ['q' => "'Kahit' means 'because'.", 'answer' => 'false', 'stars' => 7], // It means 'even if' / 'although'
            ]
        ],
        // QUIZ 2: Multiple Choice
        2 => [
            'title' => 'Nuance and Structure',
            'type' => 'mcq',
            'questions' => [
                ['q' => "Which sentence focuses on the *object* (the book)?", 'options' => ["Nagbasa si Jose ng libro.", "Binasa ni Jose ang libro.", "Magbabasa si Jose.", "Si Jose ay nagbasa."], 'answer' => 'binasa ni jose ang libro.', 'stars' => 8],
                ['q' => "How do you make a command polite?", 'options' => ["Shout the command.", "Add 'po' or 'opo'.", "Add 'na' at the end.", "Use the 'ka' pronoun."], 'answer' => "add 'po' or 'opo'.", 'stars' => 7],
                ['q' => "What does 'Marunong ka bang lumangoy?' mean?", 'options' => ["Do you want to swim?", "Do you know how to swim?", "Did you swim?", "Will you swim?"], 'answer' => 'do you know how to swim?', 'stars' => 7], // 'Marunong' = to know how (skill)
                ['q' => "What is the root word of 'pinuntahan' (went to a place)?", 'options' => ["Punta", "Tahan", "Pinun", "Unta"], 'answer' => 'punta', 'stars' => 7],
                ['q' => "Which word implies a wish or hope?", 'options' => ["Baka", "Sana", "Kasi", "Dapat"], 'answer' => 'sana', 'stars' => 7],
            ]
        ],
        // QUIZ 3: Fill in the Blanks
        3 => [
            'title' => 'Advanced Completion',
            'type' => 'fillblank',
            'questions' => [
                ['q' => "'Umalis siya _____ hindi nagpaalam.' (He/She left *without* saying goodbye.)", 'answer' => 'nang', 'stars' => 8],
                ['q' => "'Matulog ka na _____ maaga ka pa bukas.' (Sleep now *because* you have to be up early tomorrow.)", 'answer' => 'dahil', 'stars' => 7], // 'kasi' is also valid
                ['q' => "'Pakisara _____ ang pinto.' (Please close the door.)", 'answer' => 'po', 'stars' => 7],
                ['q' => "'_____ umulan, tuloy pa rin ang laro.' (Even if it rains, the game will continue.)", 'answer' => 'kahit', 'stars' => 8],
                ['q' => "(Object-Focus Command) '_____ (wash) mo ang mga plato.' (Wash the plates.)", 'answer' => 'hugasan', 'stars' => 8],
            ]
        ],
    ],
];

?>