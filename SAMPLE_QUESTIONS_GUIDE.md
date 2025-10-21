# Sample Questions Guide

## Overview
This guide contains sample questions for 5 different topics, with 20 questions each, all worth 1 point per question. These questions are designed to test knowledge in various programming and technology areas.

## Available Question Sets

### 1. Laravel Questions (20 questions)
**File**: `storage/app/templates/laravel_questions.csv`
**Topics Covered**:
- Laravel framework basics
- Commands and artisan
- Database and migrations
- Routing and middleware
- Authentication and authorization
- Blade templating
- Service providers and facades
- Configuration and environment

**Sample Questions**:
- What is Laravel?
- Which command is used to create a new Laravel project?
- What is the default database driver in Laravel?
- What is Eloquent in Laravel?

### 2. PHP Questions (20 questions)
**File**: `storage/app/templates/php_questions.csv`
**Topics Covered**:
- PHP basics and syntax
- Variables and data types
- Operators and functions
- Arrays and loops
- Form handling
- Database connections
- Error handling
- Sessions and cookies

**Sample Questions**:
- What does PHP stand for?
- What is the correct way to start a PHP script?
- Which operator is used for concatenation in PHP?
- Which function is used to output text in PHP?

### 3. Networking Questions (20 questions)
**File**: `storage/app/templates/networking_questions.csv`
**Topics Covered**:
- Network protocols (IP, TCP, HTTP, HTTPS)
- Network devices (routers, switches, hubs)
- Network types (LAN, WAN, VPN)
- Ports and services
- Security concepts (firewalls, SSH)
- Network configuration (DHCP, DNS)

**Sample Questions**:
- What does IP stand for?
- What is the default port for HTTP?
- What does DNS stand for?
- What is the purpose of a router?

### 4. Git Questions (20 questions)
**File**: `storage/app/templates/git_questions.csv`
**Topics Covered**:
- Git basics and concepts
- Repository management
- Branching and merging
- Committing and staging
- Remote repositories
- File operations
- Configuration and status

**Sample Questions**:
- What is Git?
- Which command is used to initialize a new Git repository?
- Which command is used to add files to the staging area?
- Which command is used to commit changes?

### 5. Vue.js Questions (20 questions)
**File**: `storage/app/templates/vuejs_questions.csv`
**Topics Covered**:
- Vue.js framework basics
- Components and instances
- Directives (v-bind, v-model, v-if, v-for)
- Lifecycle hooks
- Props and events
- Computed properties and watchers
- Component communication

**Sample Questions**:
- What is Vue.js?
- Which command is used to create a new Vue.js project?
- What is the main instance of a Vue.js application?
- Which directive is used to bind data to HTML elements?

### 6. Combined Questions (100 questions)
**File**: `storage/app/templates/all_topics_questions.csv`
**Description**: Contains all 100 questions from the 5 topics above in a single file for easy import.

## Question Format

All questions follow the same format:
- **Question Type**: Multiple choice
- **Points**: 1 point per question
- **Answer Options**: 4 options (A, B, C, D)
- **Correct Answer**: Number indicating the correct option (1-4)

## How to Use These Questions

### Method 1: Individual Topic Import
1. Go to Admin Panel → Import Questions
2. Choose one of the topic-specific CSV files
3. Create a quiz for that specific topic
4. Import the questions

### Method 2: Combined Import
1. Go to Admin Panel → Import Questions
2. Use the `all_topics_questions.csv` file
3. Create a comprehensive quiz with all topics
4. Import all 100 questions at once

### Method 3: Custom Selection
1. Open any of the CSV files
2. Copy and paste specific questions you want
3. Create a custom quiz with selected questions
4. Import your custom selection

## Quiz Creation Examples

### Example 1: Laravel Quiz
- **Quiz Title**: "Laravel Fundamentals Quiz"
- **Description**: "Test your knowledge of Laravel framework basics"
- **Time Limit**: 30 minutes
- **Questions**: 20 Laravel questions
- **Total Points**: 20 points

### Example 2: Full Stack Developer Quiz
- **Quiz Title**: "Full Stack Developer Assessment"
- **Description**: "Comprehensive test covering PHP, Laravel, Git, and Vue.js"
- **Time Limit**: 60 minutes
- **Questions**: 80 questions (20 from each topic)
- **Total Points**: 80 points

### Example 3: Networking Basics Quiz
- **Quiz Title**: "Networking Fundamentals"
- **Description**: "Basic networking concepts and protocols"
- **Time Limit**: 20 minutes
- **Questions**: 20 networking questions
- **Total Points**: 20 points

## Question Difficulty Levels

All questions are designed for **beginner to intermediate** level:
- **Beginner**: Basic concepts and terminology
- **Intermediate**: Practical commands and common use cases
- **Focus**: Real-world application and best practices

## Customization Options

You can easily customize these questions by:
1. **Modifying existing questions**: Edit the CSV files to change questions or answers
2. **Adding new questions**: Add more rows to the CSV files
3. **Changing point values**: Modify the Points column
4. **Creating variations**: Copy questions and modify them for different difficulty levels

## File Locations

All sample question files are located in:
```
storage/app/templates/
├── laravel_questions.csv
├── php_questions.csv
├── networking_questions.csv
├── git_questions.csv
├── vuejs_questions.csv
└── all_topics_questions.csv
```

## Import Instructions

1. **Access Import Page**: Go to Admin Panel → Import Questions
2. **Download Template**: Use the provided template to understand the format
3. **Prepare Quiz Details**: Enter quiz title, description, and time limit
4. **Upload File**: Select one of the sample CSV files
5. **Import**: Click "Import Questions" to create the quiz
6. **Review**: Check the created quiz in the Quizzes section

## Tips for Success

1. **Start Small**: Begin with individual topic quizzes
2. **Test First**: Import a few questions to test the format
3. **Customize**: Modify questions to match your specific needs
4. **Organize**: Use clear quiz titles and descriptions
5. **Time Management**: Set appropriate time limits based on question count

## Support

If you need help with importing or customizing these questions:
1. Check the Excel Import Format documentation
2. Verify the CSV file format matches the template
3. Ensure all required columns are present
4. Test with a small subset of questions first
