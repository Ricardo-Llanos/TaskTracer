
// ------ Selectores ------
const taskForm = document.querySelector('#task-form');
const taskInput = taskForm.querySelector('input');
const taskList = document.querySelector('.task__list');
const itemsLeft = document.querySelector('.task__summary');

const themeBtn = document.querySelector('.change-style');

// ------ Estado App ------
let tasks = JSON.parse(localStorage.getItem('tasks')) || [];