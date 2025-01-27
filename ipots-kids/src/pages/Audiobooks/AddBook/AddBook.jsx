import "./AddBook.css";
import { useState, useEffect } from "react";
import InputUploadButton from "../../../components/Audiobooks/InputUploadButton/InputUploadButton";
import axios from "axios";

export default function AddBook() {
  const [pageCount, setPageCount] = useState(1);
  const [categories, setCategories] = useState();
  const [animals, setAnimals] = useState();
  const [narrators, setNarrators] = useState();
  const [illustrators, setIllustrators] = useState();
  const [formData, setFormData] = useState({
    title: "",
    narrator: "",
    illustrator: "",
    category: "",
    animal: "",
    audioUrl: "www.sample.com", // placeholder link
    imageUrl: "www.sample.com", // placeholder link
    pages: [
      {
        text: "",
        imageUrl: "www.sample.com", // placeholder link
        startTime: "",
        endTime: "",
        pageNumber: 1,
      },
    ],
  });

  // Fetch categories, animals, illustrators, and narrators
  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const response = await axios.get(
          "http://localhost/ipots-kids-app/ipots-server/categories.php"
        );
        setCategories(response.data);
      } catch (error) {
        console.error("Error fetching categories:", error);
      }
    };

    const fetchAnimals = async () => {
      try {
        const response = await axios.get(
          "http://localhost/ipots-kids-app/ipots-server/animals.php"
        );
        setAnimals(response.data);
      } catch (error) {
        console.error("Error fetching animals:", error);
      }
    };

    const fetchIllustrators = async () => {
      try {
        const response = await axios.get(
          "http://localhost/ipots-kids-app/ipots-server/illustrators.php"
        );
        setIllustrators(response.data);
      } catch (error) {
        console.error("Error fetching illustrators:", error);
      }
    };

    const fetchNarrators = async () => {
      try {
        const response = await axios.get(
          "http://localhost/ipots-kids-app/ipots-server/narrators.php"
        );
        setNarrators(response.data);
      } catch (error) {
        console.error("Error fetching narrators:", error);
      }
    };

    fetchCategories();
    fetchAnimals();
    fetchIllustrators();
    fetchNarrators();
  }, []);

  // Update the page count in the form data when the page count changes
  useEffect(() => {
    setFormData((prev) => ({ ...prev, pageCount }));
  }, [pageCount]);

  // Update the form data when an input field changes
  const handleInputChange = (e) => {
    const { id, value } = e.target;
    setFormData((prev) => ({ ...prev, [id]: value }));
  };

  // Update the form data when a page field changes
  const handlePageChange = (index, field, value) => {
    setFormData((prev) => {
      const newPages = [...prev.pages];
      newPages[index] = { ...newPages[index], [field]: value };
      return { ...prev, pages: newPages };
    });
  };

  // Add a new page to the form data
  const addPage = () => {
    setFormData((prev) => ({
      ...prev,
      pages: [
        ...prev.pages,
        {
          text: "",
          imageUrl: "www.sample.com", // placeholder link
          startTime: "",
          endTime: "",
          pageNumber: prev.pages.length + 1,
        },
      ],
    }));
    setPageCount((prev) => prev + 1);
  };

  // Remove the last page from the form data
  const removePage = () => {
    setFormData((prev) => ({
      ...prev,
      pages: prev.pages
        .slice(0, -1)
        .map((page, index) => ({ ...page, pageNumber: index + 1 })),
    }));
    setPageCount((prev) => Math.max(1, prev - 1));
  };

  // Submit the form data to the server
  const handleSubmit = async (e) => {
    e.preventDefault();
    console.log(formData);
    try {
      const response = await axios.post(
        "http://localhost/ipots-kids-app/ipots-server/audiobooks.php",
        formData
      );
      console.log(response.data);
    } catch (error) {
      console.error("Error making the request:", error);
    }
  };

  return (
    <>
      {/* Header */}
      <div className="header">
        <div className="header__content">
          <h1>Add a Book</h1>
          <p>
            All audiobooks must adhere to our{" "}
            <a href="/audiobooks/guidelines">
              <u>guidelines</u>
            </a>
            .
          </p>
        </div>
      </div>

      {/* Form */}
      <div className="form-container">
        <form className="form" onSubmit={handleSubmit}>
          <div className="details">
            <div className="form-group">
              <h2>Book Details</h2>
            </div>{" "}
            {/* Title */}
            <div className="form-group">
              <div className="form-group__input">
                <label htmlFor="title">Title</label>
                <input
                  type="text"
                  id="title"
                  placeholder="e.g. Moose Goes on a Treasure Hunt"
                  value={formData.title}
                  onChange={handleInputChange}
                />
              </div>
            </div>
            {/* Narrator */}
            <div className="form-group">
              <div className="form-group__input">
                <label htmlFor="narrator">Narrator</label>
                <select
                  id="narrator"
                  value={formData.narrator}
                  onChange={handleInputChange}
                >
                  <option value="" disabled>
                    Select a narrator
                  </option>
                  {narrators &&
                    narrators.map((narrator) => (
                      <option key={narrator.id} value={narrator.id}>
                        {narrator.first_name} {narrator.last_name}
                      </option>
                    ))}
                </select>
              </div>

              {/* Illustrator */}
              <div className="form-group__input">
                <label htmlFor="illustrator">Illustrator</label>
                <select
                  id="illustrator"
                  value={formData.illustrator}
                  onChange={handleInputChange}
                >
                  <option value="" disabled>
                    Select an illustrator
                  </option>
                  {illustrators &&
                    illustrators.map((illustrator) => (
                      <option key={illustrator.id} value={illustrator.id}>
                        {illustrator.first_name} {illustrator.last_name}
                      </option>
                    ))}
                </select>
              </div>
            </div>
            {/* Category */}
            <div className="form-group">
              <div className="form-group__input">
                <label htmlFor="category">Category</label>

                <select
                  id="category"
                  value={formData.category}
                  onChange={handleInputChange}
                >
                  <option value="" disabled>
                    Select a category
                  </option>
                  {categories &&
                    categories.map((category) => (
                      <option key={category.id} value={category.id}>
                        {category.name}
                      </option>
                    ))}
                </select>
              </div>

              {/* Animal */}
              <div className="form-group__input">
                <label htmlFor="animal">Animal</label>
                <select
                  id="animal"
                  value={formData.animal}
                  onChange={handleInputChange}
                >
                  <option value="" disabled>
                    Select an animal
                  </option>
                  {animals &&
                    animals.map((animal) => (
                      <option key={animal.id} value={animal.id}>
                        {animal.name}
                      </option>
                    ))}
                </select>
              </div>
            </div>
            {/* File uploads */}
            <div className="form-group">
              {/* Cover image upload */}
              <InputUploadButton
                id="cover"
                ariaLabel="Upload book cover art"
                text="Upload book cover art"
              />

              {/* Audio file upload */}
              <InputUploadButton
                id="audio"
                ariaLabel="Upload book audio file"
                text="Upload book audio file"
              />
            </div>
            <hr />
          </div>

          {/* Page Details */}
          {formData.pages.map((page, index) => (
            <div key={index} className="page">
              <div className="form-group">
                <h2>Page #{index + 1}</h2>
              </div>

              {/* Page text */}
              <div className="form-group">
                <div className="form-group__input">
                  <label htmlFor={`page-${index + 1}`}>Page text</label>
                  <input
                    type="text"
                    id={`page-${index + 1}`}
                    placeholder="e.g. Lorem ipsum dolor sit amet"
                    value={page.text}
                    onChange={(e) =>
                      handlePageChange(index, "text", e.target.value)
                    }
                  />
                </div>
              </div>

              {/* Page image upload */}
              <div className="form-group">
                <InputUploadButton
                  id={`page-${index + 1}`}
                  ariaLabel={`Upload page graphic for page ${index + 1}`}
                  text="Upload page graphic"
                />

                {/* Audio start time */}
                <div className="form-group__input">
                  <label htmlFor={`start-time-${index + 1}`}>
                    Audio Start Time
                  </label>
                  <input
                    type="text"
                    id={`start-time-${index + 1}`}
                    placeholder="0:00"
                    value={page.startTime}
                    onChange={(e) =>
                      handlePageChange(index, "startTime", e.target.value)
                    }
                  />
                </div>

                {/* Audio end time */}
                <div className="form-group__input">
                  <label htmlFor={`end-time-${index + 1}`}>
                    Audio End Time
                  </label>
                  <input
                    type="text"
                    id={`end-time-${index + 1}`}
                    placeholder="0:30"
                    value={page.endTime}
                    onChange={(e) =>
                      handlePageChange(index, "endTime", e.target.value)
                    }
                  />
                </div>

                {/* Page number (hidden)*/}
                <input
                  type="hidden"
                  name={`page_number-${index + 1}`}
                  value={index + 1}
                />
              </div>
              <hr />
            </div>
          ))}

          {/* Buttons */}
          <div className="form-group">
            {/* Add a page */}
            <button type="button" className="button" onClick={addPage}>
              + Add Page
            </button>

            {/* Remove a page */}
            {pageCount > 1 && (
              <button type="button" className="button" onClick={removePage}>
                - Remove Page
              </button>
            )}

            {/* Submit book */}
            <button type="submit" className="button">
              Submit Book
            </button>
          </div>
        </form>
      </div>
    </>
  );
}
