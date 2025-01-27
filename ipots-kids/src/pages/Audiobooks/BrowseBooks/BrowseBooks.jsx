import { useState, useEffect } from "react";
import "./BrowseBooks.css";
import data from "../../../../public/mock-data/audiobooks.json";
import BookCard from "../../../components/Audiobooks/BookCard/BookCard";

const BrowseBooks = () => {
  const books = data.books;
  const animals = data.animals;
  const narrators = data.narrators;
  const illustrators = data.illustrators;
  const categories = data.categories;
  const users = data.users;
  const userProgress = data.user_progress;

  return (
    <div>
      <>
        {/* Header */}
        <div className="header">
          <div className="header__content">
            <h1>iPOTS Kids Audiobooks</h1>
          </div>
        </div>

        <ul className="book-cards" aria-label="Books">
          {books.map((book) => {
            const animal = animals.find(
              (animal) => animal.id === book.animal_id
            );
            const narrator = narrators.find(
              (narrator) => narrator.id === book.narrator_id
            );
            const illustrator = illustrators.find(
              (illustrator) => illustrator.id === book.illustrator_id
            );
            const category = categories.find(
              (category) => category.id === book.category_id
            );
            const progress = userProgress.find(
              (progress) => progress.book_id === book.id
            );
            const user = users.find((user) => user.id === book.user_id);
            const bookInfo = {
              book,
              animal,
              narrator,
              illustrator,
              category,
              user,
              progress,
            };
            return <BookCard key={book.id} bookInfo={bookInfo} />;
          })}
        </ul>
      </>
    </div>
  );
};

export default BrowseBooks;
