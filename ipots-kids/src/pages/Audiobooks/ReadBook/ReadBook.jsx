import { useState, useEffect } from "react";
import { useParams } from "react-router-dom";
import data from "../../../../public/mock-data/audiobooks.json";

const ReadBook = () => {
  // Get book ID from URL params
  const { bookId } = useParams();
  // Create state to store book data
  const [book, setBook] = useState(null);
  const books = data.books;

  // Fetch book data from mock data folder when bookId variable changes
  useEffect(() => {
    const fetchBook = async () => {
      const book = books.find((book) => book.id === parseInt(bookId));
      setBook(book);
    };

    fetchBook();
  }, [bookId]);

  return (
    <div className="read-book">
      {/* If book is found, display book player */}
      {book ? (
        <article>
          <h2>{book.title}</h2>
          <p>{book.pages} Pages</p>
        </article>
      ) : (
        // Else, display error message and button to return to home
        <>
          <p>Book not found</p>
          <button
            onClick={() => {
              window.location.href = "/";
            }}
          >
            Return to Home
          </button>
        </>
      )}
    </div>
  );
};

export default ReadBook;
