
<?php
include 'session_auth.php';
include('header.php');
include('sidebar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Return Books</title>
  <link rel="stylesheet" href="returnbooks.css">
</head>
<body>

<main class="main-content">
  <h2 class="page-title">Return Books</h2>

<div class="search-container">
  <input type="text" id="search" placeholder="Search by Student ID or Name...">
  <button id="clear-btn" class="clear-btn">&times;</button>
  <button id="search-btn">Search</button>
  <div class="suggestions" id="suggestions"></div>
</div>


<div id="noResults" class="no-results" style="color: red; text-align: center; margin-top: 10px;"></div>



<div class="result" id="result">
  <!-- Student Info Placeholder -->
<div class="card student-info" id="studentInfo">
  <table class="info-table">
    <thead>
      <tr>
        <th>Student ID Number</th>
        <th>Name</th>
        <th>Program / Strand</th>
        <th>Year Level</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td id="infoId">—</td>
        <td id="infoName">—</td>
        <td id="infoCourse">—</td>
        <td id="infoYear">—</td>
      </tr>
    </tbody>
  </table>
</div>


  <!-- Borrowed Books Placeholder -->
  <div class="card" id="borrowedBooksCard">

    <h3>Borrowed Books</h3>
    <table border="1" cellpadding="6" cellspacing="0" id="borrowedBooksTable">
      <thead>
        <tr>
          <th>Book ID</th>
          <th>Title</th>
          <th>Borrow Date</th>
          <th>Due Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="borrowedBooksBody">
        <tr><td colspan="6" class="text-center text-muted">No data available</td></tr>
      </tbody>
    </table>
  </div>
</div>

</main>


  <script>
    const searchInput = document.getElementById('search');
    const searchBtn = document.getElementById('search-btn');
    const suggestionsBox = document.getElementById('suggestions');
    const resultBox = document.getElementById('result');

 // Fetch suggestions while typing
searchInput.addEventListener('keyup', (e) => {
  const query = searchInput.value.trim();

  // Stop showing suggestions when pressing Enter
  if (e.key === 'Enter') {
    suggestionsBox.innerHTML = '';
    return;
  }

  if (query.length === 0) {
    suggestionsBox.innerHTML = '';
    return;
  }

  fetch(`returnsearch.php?q=${encodeURIComponent(query)}`)
    .then(res => res.json())
    .then(data => {
      suggestionsBox.innerHTML = '';
      data.forEach(item => {
        const div = document.createElement('div');
        div.textContent = `${item.student_id_no} - ${item.name}`;
        div.onclick = () => selectStudent(item);
        suggestionsBox.appendChild(div);
      });
    });
});


    // Search on Enter key
    searchInput.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        e.preventDefault();
        suggestionsBox.innerHTML = ''; // hide suggestions when pressing Enter

        searchStudent(searchInput.value);
      }
    });

    // Search on button click
    searchBtn.addEventListener('click', () => {
      suggestionsBox.innerHTML = ''; // hide suggestions when clicking Search

      searchStudent(searchInput.value);
    });

    function selectStudent(student) {
      searchInput.value = `${student.student_id_no} - ${student.name}`;
      suggestionsBox.innerHTML = '';
      showResult(student);
    }

    function searchStudent(query) {
      fetch(`returnsearch.php?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
if (data.length > 0) {
  document.getElementById('noResults').textContent = ''; // clear message
  showResult(data[0]);
} else {
  document.getElementById('noResults').textContent = 'No results found.';
  
  // Reset fields but keep layout
  document.getElementById('infoId').textContent = '—';
  document.getElementById('infoName').textContent = '—';
  document.getElementById('infoCourse').textContent = '—';
  document.getElementById('infoYear').textContent = '—';
  document.getElementById('borrowedBooksBody').innerHTML =
    '<tr><td colspan="6" class="text-center text-muted">No data available</td></tr>';
}


        });
    }

function showResult(student) {
  // Update Student Info
  document.getElementById('infoId').textContent = student.student_id_no;
  document.getElementById('infoName').textContent = student.name;
  document.getElementById('infoCourse').textContent = student.course;
  document.getElementById('infoYear').textContent = student.year_level;

  // Show borrowed books section
  showBorrowedBooks(student);
}


function showBorrowedBooks(student) {
  const borrowedCard = document.getElementById('borrowedBooksCard');
  const tbody = document.getElementById('borrowedBooksBody');

  borrowedCard.style.display = 'block'; // show section

  if (!student.borrowed_books || student.borrowed_books.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">No borrowed books found.</td></tr>`;
    return;
  }

  tbody.innerHTML = ''; // clear old rows

  student.borrowed_books.forEach(book => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${book.book_id}</td>
      <td>${book.title}</td>
      <td>${book.borrow_date}</td>
      <td>${book.due_date}</td>
      <td>${book.status}</td>
      <td><button class="return-btn" data-book-id="${book.book_id}" data-student-id="${student.student_id_no}">Return</button></td>
    `;
    tbody.appendChild(tr);
  });

  // Attach return events
  document.querySelectorAll('.return-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const bookId = btn.getAttribute('data-book-id');
      const studentId = btn.getAttribute('data-student-id');
      processReturn(bookId, studentId);
    });
  });
}


// Function to handle Return click
function processReturn(bookId, studentId) {
  if (!confirm('Confirm return of this book?')) return;

  fetch('returnbook_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `book_id=${encodeURIComponent(bookId)}&student_id_no=${encodeURIComponent(studentId)}`
  })
  .then(res => res.text())
  .then(data => {
    alert(data); // show success or error message
    searchStudent(studentId); // refresh results after returning
  })
  .catch(err => console.error(err));
}




    
    const clearBtn = document.getElementById('clear-btn');

    // Show or hide the clear button while typing
searchInput.addEventListener('input', () => {
  clearBtn.style.display = searchInput.value ? 'block' : 'none';
  document.getElementById('noResults').textContent = ''; // clear message
});


    // Clear the result box when the input is empty
// Keep the tables visible, only reset their content
searchInput.addEventListener('input', () => {
  if (searchInput.value.trim() === '') {
    suggestionsBox.innerHTML = ''; // hide suggestions only

    // Reset displayed info instead of removing the whole section
    document.getElementById('infoId').textContent = '—';
    document.getElementById('infoName').textContent = '—';
    document.getElementById('infoCourse').textContent = '—';
    document.getElementById('infoYear').textContent = '—';
    document.getElementById('borrowedBooksBody').innerHTML =
      '<tr><td colspan="6" class="text-center text-muted">No data available</td></tr>';
  }
});



    // Clear input, suggestions, and results when clicked
clearBtn.addEventListener('click', () => {
  searchInput.value = '';
  clearBtn.style.display = 'none';
  searchInput.focus();

  suggestionsBox.innerHTML = '';

  // Instead of removing the entire result box, just reset the fields
  document.getElementById('infoId').textContent = '—';
  document.getElementById('infoName').textContent = '—';
  document.getElementById('infoCourse').textContent = '—';
  document.getElementById('infoYear').textContent = '—';

  // Reset the borrowed books table to default
  document.getElementById('borrowedBooksBody').innerHTML =
    '<tr><td colspan="6" class="text-center text-muted">No data available</td></tr>';

    location.reload();

});

  </script>

</body>
</html>
