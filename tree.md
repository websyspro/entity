ExpressionNode
│
├── ExpressionLogical
│   │
│   ├── left: ExpressionNode
│   ├── operator: && || and or
│   └── right: ExpressionNode
│
├── ExpressionCompare
│   │
│   ├── left: ExpressionNode
│   ├── operator:
│   │      ===
│   │      !==
│   │      >
│   │      <
│   │      >=
│   │      <=
│   │
│   └── right: ExpressionNode
│
├── ExpressionUnary
│   │
│   ├── operator:
│   │      !
│   │
│   └── expression: ExpressionNode
│
├── ExpressionGroup
│   │
│   └── nodes: ExpressionNode[]
│
├── ExpressionSubQuery
│   │
│   ├── source: ExpressionProperty
│   ├── method:
│   │      any
│   │      all
│   │      where
│   │      select
│   │
│   └── expression: ExpressionNode
│
├── ExpressionCall
│   │
│   ├── callee: ExpressionNode
│   └── arguments: ExpressionNode[]
│
├── ExpressionProperty
│   │
│   ├── parameter:
│   │      $i
│   │      $o
│   │
│   └── path:
│          [
│             "itemsProposta",
│             "Created",
│             "Id"
│          ]
│
├── ExpressionValue
│   │
│   ├── type:
│   │      string
│   │      int
│   │      float
│   │      bool
│   │      null
│   │      enum
│   │      variable
│   │
│   └── value: mixed
│
├── ExpressionVariable
│   │
│   └── name:
│          $startDate
│          $test
│
├── ExpressionEnum
│   │
│   ├── enum:
│   │      Status
│   │
│   └── case:
│          Aprovada
│
├── ExpressionStringInterpolated
│   │
│   └── parts:
│          [
│             "Teste ",
│             ExpressionVariable($test)
│          ]
│
├── ExpressionArray
│   │
│   └── items: ExpressionNode[]
│
├── ExpressionNumber
│   │
│   └── value:
│          123
│          9098767
│
├── ExpressionBoolean
│   │
│   └── value:
│          true
│          false
│
├── ExpressionNull
│
├── ExpressionParameter
│   │
│   ├── type:
│   │      PropostaEntity
│   │
│   └── name:
│          $i
│
├── ExpressionLambda
│   │
│   ├── parameters: ExpressionParameter[]
│   └── body: ExpressionNode
│
└── ExpressionRoot
    │
    ├── parameters: ExpressionParameter[]
    └── body: ExpressionNode