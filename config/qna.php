<?php


return [
       'campaign' => [
            'faq' => [
                'title' => 'Faq',
                    'validate' => [
                         'role_id' => 'required',
                    ]
                ],
            'support' => [
                'title' => 'Support',
                'validate' => [
                        'role_id' => 'required',
                    ],
                ],
           ],
            
        'questions' => [
            'title' => 'Questions',
            'faq' => [
                'validate' => [
                   'campaign.*.question' => 'required'
                    ]
                ],
    
            'support' => [
                'validate' => [
                    'campaign.*.question' => 'required'
                    ]
                ]
            ],
            
        'answers' => [
            'title' => 'Answers',
            'validate' => [
                'answers' => 'required',
                'is_ans' => 'required',
                'question_id' => 'required',
                ]
            ],
        'listing' => [
            'title' => 'Listing',
            'faq' => [
                'validate' => [
                    'campaign_code' => 'required'
                    ]
                ]
            ]
        ];
