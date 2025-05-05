<?php

/**
 * Tabela alunos e voce quer fazer INNER JOIN com pessoas, usando o critério pessoa_id no alunos no id da pessoas
 * Esse return é o WITH enviado no construtor
 */

return [
    [
        'identifier' => 'pessoas',
        'clauses' => [
            [
                "field" => "deleted",
                "operator" => "=",
                "value" => "N"
            ],
        ],
        'connections' => [
            [
                'source' => 'pessoa_id',
                'reference' => 'id'
            ],
        ]
    ],
    [
        'identifier' => 'instituicoes',
        'clauses' => [],
        'connections' => [
            [
                'source' => 'instituicao_id',
                'reference' => 'id',
            ]
        ]
    ]
];