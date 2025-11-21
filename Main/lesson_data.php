<?php
// =========================================
// FILIPINO LANGUAGE LEARNING LESSON DATA
// =========================================
// Each level corresponds to a quiz level, and each lesson
// provides the concepts tested in the quiz of the same number.

$lesson_data = [
    // --- LEVEL 1: Mga Pangunahin (Basics) ---
    1 => [
        // LESSON 1 (for True/False Quiz)
        1 => [
            'title' => 'Basic Vocabulary and Opposites',
            'sections' => [
                'Overview' => 'This lesson introduces foundational Filipino words used in daily life, including common adjectives, animals, and simple affirmations/negations.',
                'Key Vocabulary' => [
                    'Maganda' => 'Beautiful. (e.g., "Magandang umaga" - "Beautiful morning")',
                    'Salamat' => 'Thank you. (The standard way to show gratitude)',
                    'Oo / Hindi' => "'Oo' (oh-oh) means 'Yes'. 'Hindi' (hin-deh) means 'No'.",
                    'Aso / Pusa' => "'Aso' is the word for 'Dog'. 'Pusa' is the word for 'Cat'.",
                ],
                'Key Concept: Antonyms' => 'Antonyms are words with opposite meanings. A common pair is "Malaki" (Big) and "Maliit" (Small).',
            ]
        ],

        // LESSON 2 (for Multiple Choice Quiz)
        2 => [
            'title' => 'Common Phrases and Questions',
            'sections' => [
                'Overview' => 'Learn the most essential phrases for greeting people and asking simple questions.',
                'Common Greetings' => [
                    'Kumusta ka?' => "The most common greeting, meaning 'How are you?'",
                    'Magandang umaga' => 'Good morning.',
                    'Magandang hapon' => 'Good afternoon.',
                    'Magandang gabi' => "Good evening / Good night.",
                ],
                'Useful Words & Questions' => [
                    'Masaya' => 'Happy. (The opposite is "Malungkot" - Sad)',
                    'Tubig' => 'Water.',
                    'Saan ka pupunta?' => "A very common question meaning 'Where are you going?'",
                ],
            ]
        ],

        // LESSON 3 (for Fill in the Blanks Quiz)
        3 => [
            'title' => 'More Essential Nouns and Phrases',
            'sections' => [
                'Overview' => 'Expand your vocabulary with more common nouns and one of the most important phrases in any language.',
                'Key Vocabulary' => [
                    'Bahay' => 'House.',
                    'Guro' => 'Teacher. (From Spanish "maestro/a")',
                    'Paalam' => 'Goodbye. (Used for more formal or long-term goodbyes)',
                ],
                'Key Phrase: Mahal Kita' => [
                    'Mahal kita' => "This phrase means 'I love you'.",
                    'Mahal' => 'Means "love" and also "expensive".',
                    'Kita' => 'A special pronoun that combines "I" (ko) and "you" (ka). It is used when the "I" is the actor and "you" are the object.',
                ],
            ]
        ],
    ],

    // --- LEVEL 2: Pagbuo ng Pangungusap (Sentence Building) ---    
    2 => [
        // LESSON 1 (for True/False Quiz)
        1 => [
            'title' => 'Simple Sentence Structure and Markers',
            'sections' => [
                'Overview' => 'Filipino sentence structure is very flexible. A common pattern is Verb-Actor-Object, but the "Ang" marker identifies the "subject" or "topic" of the sentence.',
                'Key Concepts' => [
                    'Basic Sentence' => "'Kumakain ako ng tinapay.' (I am eating bread.) 'Kumakain' (Verb) 'ako' (Actor) 'ng tinapay' (Object).",
                    'Ang Marker' => "'Ang' marks the topic of the sentence. In 'Masaya ang mga bata,' ('The children are happy'), the topic is 'ang mga bata' (the children).",
                    'Si / Sina Markers' => "Use 'Si' for one person's name (e.g., 'Si Juan'). Use 'Sina' for multiple people (e.g., 'Sina Juan at Maria').",
                    'Gusto Ko' => "'Gusto' means 'want' or 'like'. 'Gusto ko ng kape' means 'I want coffee.'",
                ],
            ]
        ],

        // LESSON 2 (for Multiple Choice Quiz)
        2 => [
            'title' => 'Pronouns and Case Markers (Ang, Ng, Sa)',
            'sections' => [
                'Overview' => 'The markers "ang," "ng," and "sa" are crucial. They show the role of a word in a sentence.',
                'Pronouns (Panghalip)' => [
                    'Ako' => 'I (Ang-form, the topic)',
                    'Ikaw / Ka' => 'You (Ang-form)',
                    'Siya' => 'He / She (Ang-form)',
                    'Sila' => 'They (Ang-form)',
                ],
                'Case Markers' => [
                    'Ang / Si' => 'Marks the topic/subject. (e.g., "Malaki ANG aso.")',
                    'Ng / Ni' => 'Marks the object OR the actor (if not the topic). (e.g., "Kumain NG isda.")',
                    'Sa / Kay' => 'Marks the location, direction, or beneficiary. (e.g., "Pumunta SA simbahan.")',
                ],
                'Identifying Sentence Parts' => "In 'Bumili ng isda si Maria' (Maria bought fish), 'Bumili' is the Verb, 'si Maria' is the Actor (topic), and 'ng isda' is the Object.",
            ]
        ],

        // LESSON 3 (for Fill in the Blanks Quiz)
        3 => [
            'title' => 'Possessive Pronouns and Simple Sentences',
            'sections' => [
                'Overview' => 'Learn how to show possession and form more complete thoughts.',
                'Possessive Pronouns (Ng-form)' => [
                    'ko' => 'my (e.g., "Gusto KO matulog." - I want to sleep.)',
                    'mo' => 'your (e.g., "Nasaan ang susi MO?" - Where is your key?)',
                    'niya' => 'his / her',
                    'akin / aking' => 'my (used before the noun, e.g., "Ito ay AKING pusa." - This is my cat.)',
                ],
                'Describing Actions' => [
                    'Umuulan nang malakas.' => 'It is raining hard. "Nang" is used as an adverb to describe the verb "Umuulan".',
                    'Magbabasa siya ng libro.' => 'He/She will read a book. "Siya" (He/She) is the actor.',
                ],
            ]
        ],
    ],

    // --- LEVEL 3: Gramatika (Grammar) - Tenses & Conjunctions ---
    3 => [
        // LESSON 1 (for True/False Quiz)
        1 => [
            'title' => 'Introduction to Filipino Verb Tenses',
            'sections' => [
                'Overview' => 'Filipino verbs are conjugated for tense (aspect) by adding affixes and/or repeating syllables from the root word.',
                'The 3 Main Tenses (Aspects)' => [
                    'Past (Naganap)' => 'The action is completed. (e.g., "Kumain" - Ate. Root: kain)',
                    'Present (Nagaganap)' => 'The action is ongoing. (e.g., "Tumatakbo" - Running. Root: takbo)',
                    'Future (Magaganap)' => 'The action has not yet happened. (e.g., "Lalakad" - Will walk. Root: lakad)',
                ],
                'Connectors (Pang-ugnay)' => [
                    'Pero' => 'Means "but". Used to show contrast. (e.g., "Gusto ko, pero mahal." - I like it, but it\'s expensive.)',
                    'Ng vs. Nang' => "'Ng' marks objects. 'Nang' is an adverb (describes verbs) or means 'when'.",
                ],
            ]
        ],

        // LESSON 2 (for Multiple Choice Quiz)
        2 => [
            'title' => 'Forming Tenses and Using Conjunctions',
            'sections' => [
                'Overview' => 'Learn the common patterns for changing a verb\'s tense.',
                'Verb Conjugation (for -UM- verbs)' => [
                    'Root' => 'kain (eat)',
                    'Past' => 'k-UM-ain (Kumain)',
                    'Present' => 'k-UM-a-kain (Kumakain - repeat first syllable of root)',
                    'Future' => 'ka-kain (Kakain - repeat first syllable of root)',
                ],
                'Common Conjunctions' => [
                    'Kaya' => 'So / Therefore. Shows a result. (e.g., "Nag-aral siya, KAYA mataas ang grado.")',
                    'Pero' => 'But. Shows contrast. (e.g., "Gusto ko, PERO ayaw niya.")',
                    'At' => 'And. (e.g., "Aso AT pusa.")',
                    'O' => 'Or. (e.g., "Kape O gatas.")',
                ],
            ]
        ],

        // LESSON 3 (for Fill in the Blanks Quiz)
        3 => [
            'title' => 'Applying Grammar in Sentences',
            'sections' => [
                'Overview' => 'Use the correct tenses and markers to build grammatically correct sentences.',
                'Applying Tenses' => [
                    'Past' => "'PUMUNTA kami sa mall kahapon.' (We went to the mall yesterday.)",
                    'Present' => "'NANONOOD siya ng TV ngayon.' (He/She is watching TV right now.)",
                    'Future' => "'KAKANTA ako bukas.' (I will sing tomorrow.)",
                ],
                'Markers for People (Kay / Kaysa kay)' => [
                    'Kay' => "Use 'kay' instead of 'sa' for people. (e.g., 'Ibigay mo ito KAY Maria.' - Give this to Maria.)",
                    'Kaysa kay' => "Use 'kaysa kay' for comparisons between people. (e.g., 'Mas matangkad si Juan KAYSA KAY Pedro.' - Juan is taller than Pedro.)",
                ],
            ]
        ],
    ],

    // --- LEVEL 4: Mas Malalim (Deeper Concepts) ---
    4 => [
        // LESSON 1 (for True/False Quiz)
        1 => [
            'title' => 'Grammatical Focus (Actor vs. Object)',
            'sections' => [
                'Overview' => 'Filipino grammar uses "focus" to show which part of the sentence is the topic. This changes the verb used.',
                'Actor Focus' => "The actor is the topic ('ang'). The verb often uses affixes like -UM- or MAG-. (e.g., 'BUMILI ang bata ng laruan.' - THE CHILD bought a toy.)",
                'Object Focus' => "The object is the topic ('ang'). The verb often uses affixes like -IN, -AN, or i-. (e.g., 'KINAIN ng pusa ang isda.' - The fish was eaten BY THE CAT. Lit: 'Was-eaten by-cat THE FISH.')",
                'Nuance Words' => [
                    'Sana' => 'Expresses hope or a wish. (e.g., "SANA manalo ako." - I hope I win.)',
                    'Daw / Raw' => 'Reportedly / They said. Use "daw" after consonants, "raw" after vowels. (e.g., "Umalis DAW siya." / "Aalis RAW sila.")',
                    'Kahit' => 'Even if / Although.',
                ],
            ]
        ],

        // LESSON 2 (for Multiple Choice Quiz)
        2 => [
            'title' => 'Nuance, Politeness, and Root Words',
            'sections' => [
                'Identifying Focus' => [
                    'Actor Focus' => "'Nagbasa si Jose ng libro.' (Jose is the 'ang' topic).",
                    'Object Focus' => "'Binasa ni Jose ang libro.' (Ang libro is the 'ang' topic).",
                ],
                'Politeness (Po / Opo)' => [
                    'Po' => 'A particle used to show respect to elders or superiors. Placed within sentences.',
                    'Opo' => 'A polite "Yes". ("Oo" + "po").',
                    'Commands' => "Add 'po' to make commands polite. (e.g., 'Pakisara po ang pinto.' - Please close the door.)",
                ],
                'Knowing vs. Knowing How' => [
                    'Marunong' => 'To know *how* to do something (a skill). (e.g., "Marunong ka bang lumangoy?" - Do you know how to swim?)',
                    'Alam' => 'To know a fact or information. (e.g., "Alam ko ang sagot." - I know the answer.)',
                ],
                'Root Words (Salitang-ugat)' => 'Most Filipino words are built from a root. The root of "pinuntahan" (went to a place) is "punta" (go).',
            ]
        ],

        // LESSON 3 (for Fill in the Blanks Quiz)
        3 => [
            'title' => 'Advanced Connectors and Commands',
            'sections' => [
                'Overview' => 'Use more complex words to connect ideas and give object-focused commands.',
                'Advanced Connectors' => [
                    'Nang (adverb)' => "Used to describe *how* an action is done. (e.g., 'Umalis siya NANG hindi nagpaalam.' - He left *without* saying goodbye.)",
                    'Dahil / Kasi' => "Because. (e.g., 'Matulog ka na DAHIL maaga ka pa bukas.' - Sleep now because you have to be up early tomorrow.)",
                    'Kahit' => "Even if. (e.g., 'KAHIT umulan, tuloy ang laro.' - Even if it rains, the game will continue.)",
                ],
                'Polite Commands' => [
                    'Paki-' => 'An affix added to a verb root to mean "please". (e.g., "Pakisara po ang pinto." - Please close the door.)',
                ],
                'Object-Focus Commands' => 'These commands focus on the object, not the person. (e.g., "HUGASAN mo ang mga plato." - Wash the plates. The verb "hugasan" is focused on "ang mga plato".)',
            ]
        ],
    ],
];

?>