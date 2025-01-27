import "./BookCard.css";

const BookCard = (props) => {
  const { bookInfo } = props;

  return (
    <li key={bookInfo.book.id}>
      <a href={`/audiobooks/play/${bookInfo.book.id}`} className="book-card">
        {/* Book Cover */}
        <img
          src={`../../../../mock-data${bookInfo.book.image_path}`}
          alt={bookInfo.book.title}
          className="book-card__cover"
        />

        <div className="book-card__info">
          {/*Book Title */}
          <h2>{bookInfo.book.title}</h2>

          {/* Category */}
          <p aria-label={`${bookInfo.category.name} Category`}>
            {bookInfo.category.name}
          </p>

          {/* # of Pages */}
          <p>{bookInfo.book.pages} Pages</p>

          {/* Progress Bar */}
          {bookInfo.progress && (
            <>
              <progress value={bookInfo.progress.progress} max="100"></progress>
              <p className="sr-only">
                Page{" "}
                {Math.round(
                  (bookInfo.progress.progress / 100) * bookInfo.book.pages
                )}{" "}
                of {bookInfo.book.pages}
              </p>
            </>
          )}
        </div>
      </a>
    </li>
  );
};

export default BookCard;
