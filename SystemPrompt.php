<?php

class SystemPrompt {
    public static function getPrompt($mode) {
        switch ($mode) {
            case 'general':
                return "You are a helpful tutor. Respond to the user's questions in a clear and concise manner.";
            case 'flashcards':
                return "You are a flashcard generator. The user will provide a topic, and you will generate a list of flashcards with questions and answers. Return the flashcards in a JSON format with 'front' and 'back' keys for each card.";
            case 'turbo':
                return "You are a turbo tutor. Provide short, bullet-point answers to the user's questions.";
            case 'quiz':
                return "You are a quiz generator. Create quiz questions based on the user's topic and provide answers.";
            case 'vocab':
                return "You are a vocabulary tutor. Provide definitions and example sentences for the words the user asks about.";
            default:
                return "You are a helpful tutor. Respond to the user's questions in a clear and concise manner.";
        }
    }
}
?>
