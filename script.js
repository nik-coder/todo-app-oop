
text/x-generic script.js ( ASCII text, with CRLF line terminators )
$(document).ready(function() {
    const apiUrl = 'api/tasks.php';

    function loadTasks() {
        $.get(apiUrl, function(data) {
            $('#taskList').empty();
            data = JSON.parse(data);
            data.forEach(task => {
                $('#taskList').append(`
                    <li>
                        <p class="taskItem">${task.task}<p>
                        <button class="deleteBtn" data-id="${task.id}">Delete</button>&nbsp;&nbsp;<button class="updateBtn" data-id="${task.id}">Update</button>
                    </li>
                `);
            });
        });
    }

    $('#addTaskBtn').click(function() {
        const taskInput = $('#taskInput').val();
        if (taskInput.trim() === '') {
            alert('Task cannot be empty!');
            return;
        }
        const newTask = { task: taskInput };
        $.post(apiUrl, JSON.stringify(newTask), function(data) {
            loadTasks();
            $('#taskInput').val('');
        }, 'json');
    });

    $(document).on('click', '.deleteBtn', function() {
        const taskId = $(this).data('id');
        $.ajax({
            url: apiUrl,
            type: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({ id: taskId }),
            success: function() {
                loadTasks();
            }
        });
    });
    
    // Update task on click
    $(document).on('click', '.updateBtn', function() {
        const taskId = $(this).data('id');
        const taskInput = $('#taskInput').val();
        $.ajax({
            url: apiUrl,
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({ id: taskId, task: taskInput }),
            success: function() {
                loadTasks();
                $('#taskInput').val('');
            }
        });
    });


    // Initial load
    loadTasks();
});