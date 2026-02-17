# Excel Import Format for Quiz Questions

## Overview
This document explains the Excel format required for importing quiz questions into the system.

## File Requirements
- **Supported formats**: .xlsx, .xls, .csv
- **Maximum file size**: 10MB
- **First row**: Must contain headers (column names)

## Column Structure

| Column | Header | Required | Description | Example |
|--------|--------|----------|-------------|---------|
| A | Question Text | Yes | The question text | "What is the capital of France?" |
| B | Question Type | Yes | Type of question | multiple_choice, true_false, text |
| C | Points | No | Points for the question (default: 1) | 10 |
| D | Answer 1 | No | First answer option | London |
| E | Answer 2 | No | Second answer option | Berlin |
| F | Answer 3 | No | Third answer option | Paris |
| G | Answer 4 | No | Fourth answer option | Madrid |
| H | Correct Answer | No | Number of correct answer (1-4) | 3 |

## Question Types

### 1. Multiple Choice (`multiple_choice`)
- **Description**: Questions with 2-4 answer options
- **Required columns**: Question Text, Question Type, Answer 1-4, Correct Answer
- **Correct Answer**: Number (1-4) indicating which answer is correct
- **Example**:
  ```
  Question Text: "What is the capital of France?"
  Question Type: "multiple_choice"
  Answer 1: "London"
  Answer 2: "Berlin" 
  Answer 3: "Paris"
  Answer 4: "Madrid"
  Correct Answer: "3"
  ```

### 2. True/False (`true_false`)
- **Description**: Questions with True/False options
- **Required columns**: Question Text, Question Type, Answer 1, Answer 2, Correct Answer
- **Answer 1**: Should be "True"
- **Answer 2**: Should be "False"
- **Correct Answer**: 1 for True, 2 for False
- **Example**:
  ```
  Question Text: "PHP is a server-side language."
  Question Type: "true_false"
  Answer 1: "True"
  Answer 2: "False"
  Correct Answer: "1"
  ```

### 3. Text/Essay (`text`)
- **Description**: Open-ended questions requiring text responses
- **Required columns**: Question Text, Question Type
- **Optional columns**: Points
- **Answer columns**: Leave empty
- **Correct Answer**: Leave empty
- **Example**:
  ```
  Question Text: "Explain the concept of OOP."
  Question Type: "text"
  Points: "15"
  ```

## Sample Excel File

| Question Text | Question Type | Points | Answer 1 | Answer 2 | Answer 3 | Answer 4 | Correct Answer |
|---------------|---------------|--------|----------|----------|----------|----------|----------------|
| What is the capital of France? | multiple_choice | 10 | London | Berlin | Paris | Madrid | 3 |
| PHP is a server-side language. | true_false | 5 | True | False | | | 1 |
| Explain the concept of OOP. | text | 15 | | | | | |
| What is 2 + 2? | multiple_choice | 5 | 3 | 4 | 5 | 6 | 2 |
| JavaScript is a programming language. | true_false | 5 | True | False | | | 1 |
| Describe the MVC pattern. | text | 20 | | | | | |

## Import Process

1. **Access Import Page**: Go to Admin Panel → Import Questions
2. **Download Template**: Click "Download Excel Template" to get the correct format
3. **Fill Quiz Details**: Enter quiz title, description, and time limit
4. **Upload File**: Select your Excel file with questions
5. **Import**: Click "Import Questions" to process the file
6. **Review**: Check the created quiz in the Quizzes section

## Validation Rules

- **Quiz Title**: Required, maximum 255 characters
- **Question Text**: Required for each question
- **Question Type**: Must be one of: multiple_choice, true_false, text
- **Points**: Must be a positive integer (default: 1)
- **Correct Answer**: Must be 1-4 for multiple choice and true/false questions
- **File Size**: Maximum 10MB
- **File Format**: .xlsx, .xls, or .csv

## Tips

1. **Use the template**: Download the provided template to ensure correct format
2. **Check question types**: Use exact values: multiple_choice, true_false, text
3. **Validate answers**: Ensure correct answer numbers match available options
4. **Test with small files**: Start with a few questions to test the format
5. **Backup data**: Always backup existing quizzes before bulk imports

## Troubleshooting

### Common Errors
- **"Question Type not recognized"**: Use exact values: multiple_choice, true_false, text
- **"Correct Answer out of range"**: Use numbers 1-4 only
- **"File too large"**: Reduce file size or split into multiple files
- **"Invalid format"**: Ensure first row contains headers

### Support
If you encounter issues, check:
1. File format and size
2. Column headers match exactly
3. Question types use correct values
4. Required fields are filled
5. Correct answer numbers are valid
