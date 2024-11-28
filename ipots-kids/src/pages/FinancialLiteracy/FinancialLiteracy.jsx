import React from "react";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faBook, faPlay } from "@fortawesome/free-solid-svg-icons";

export default function FinancialLiteracy() {
  return (
    <div className=" d-flex flex-column justify-content-center align-items-center">
      {/* Replace with user avatar */}
      <img src="../images/avartars/iaccess/39.png" alt="User avatar" />

      <button className=" button-format buttonColor">
        <FontAwesomeIcon icon={faBook} /> {"  "}Read
      </button>

      <button
        className=" button-format buttonColor"
        onClick={() => (window.location.href = "/financial-literacy/play")}
      >
        <FontAwesomeIcon icon={faPlay} /> {"  "}Play
      </button>

      <button className=" button-format buttonColor-2">
        Printable Activities
      </button>
    </div>
  );
}
