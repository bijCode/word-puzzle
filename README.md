# word-puzzle
I have implemented tokenised authentication for students and Dictionary API to check valid words.
After registeration of student, student need to login. If there is a puzzle available student can play with puzzle by submitting 
each words and by the end total score will be shown. if there is no puzzle they can create one. Also students can see the top
score in each puzzle with word and score

# To run project follow the steps 
# Run these commands in cmd

git clone https://github.com/bijCode/word-puzzle.git
composer install

# copy .env.example file into .env
# Add database configuration according to your machine 
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=word_puzzle
DB_USERNAME=root
DB_PASSWORD=

# Run these commands
php artisan key:generate
php artisan migrate
php artisan db:seed

# This will create a user with 
# email : user@wordpuzzle.com
# password : password123

# Apis (if you are testing Postman please add Accept:application/json to get correct response from api)
# login (after login add the token to header)
/api/login  - POST
# create a puzzle if there is no puzzle available (lenght is option by default is 12)
/api/puzzle/{length?} - GET
/api/puzzle - POST( To get details of puzzle, parameters:id)
# start puzzle with puzzle id and word as parameters to send (here word will be validated within the string or a valid word)
/api//puzzle/{puzzle}/submit - POST (parameters:word)
# To end the game and see overall score 
/api/puzzle/{puzzle}/end - POST
# to see the top 10 score in the puzzle in which words and score will be shown in desc order
/api/leaderboard/{puzzle} - GET

# Unit Test cases added

✓ puzzle generation
✓ invalid word submission
✓ duplicate letter usage 
✓ leaderboard returns top scores
✓ end game returns correct info 

# Inclusions 

1. Each puzzle is defined by a random string of letters that is presented to the user,
for example "dgeftoikbvxuaa". The string is guaranteed to allow the construction of
at least one valid English word.
2. Students attempt to create English words using the letters provided in the
string. Each letter used scores one point.
a. For example fox would score 3 points
3. Letters can only be used as many times as they appear in the string. Once a
letter is used in a submitted word, it cannot be used in subsequent resubmissions
by the same student.
a. If a student used fox they would have dgetikbvuaa left to play with.
4. A word has to be a valid English word, consider how this would be validated.
5. When there are no characters left in the string, or the student chooses to end the
test, the system will show them their score.
6. The game maintains the top ten highest-scoring submissions (words and
score). Anyone using the system should be able to request this list.
7. Duplicate words are not allowed in the high score list. A word can only appear
once in the high score list.

# Exclusion 
if there were any valid words remaining in the string.
For this we need a file or database with large set of words 
Eventhough I have implemented the logic in app/Services/PuzzleService/findPossibleWords()
