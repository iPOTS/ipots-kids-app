import "./InputUploadButton.css";
import { useRef } from "react";

const InputUploadButton = ({ id, ariaLabel, text }) => {
  const fileInputRef = useRef(null);

  const handleButtonClick = () => {
    fileInputRef.current.click();
  };

  return (
    <div className="form-group__input">
      <input
        type="file"
        ref={fileInputRef}
        style={{ display: "none" }}
        aria-label={{ ariaLabel }}
      />
      <button
        type="button"
        style={{ cursor: "pointer" }}
        className="button"
        onClick={handleButtonClick}
      >
        {text}
      </button>
    </div>
  );
};

export default InputUploadButton;
