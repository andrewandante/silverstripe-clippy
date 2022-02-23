import React, { useEffect, useState } from 'react';
import parse from 'html-react-parser';

const UserGuide = ({ subscribe }) => {
  const [show, setShow] = useState(0);
  const [content, setContent] = useState('');

  useEffect(() => {
      // Used on first page load to automatically show the user guide
      if (subscribe.value) {
        setShow(parseInt(subscribe.value, 10));
      }

      // Used when the cms header tabs are clicked
      document.addEventListener('userguide:change', (event) => {
        setShow(event.detail);
      });
    },
    []
  );

  useEffect(() => {
    const href = document.location.href;
    const link = href.replace('/admin/pages/guide/show/', '/admin/pages/guide/markdown/');

    fetch(link)
      .then(response => response.json())
      .then(data => setContent(data.Content));
  }, [show]);

  return (
    <div
      style={{ display: show ? 'block' : 'none' }}
      className={'panel panel--padded panel--scrollable flexbox-area-grow cms-content-fields'}
    >
      {parse(content)}
    </div>
  );
};

export default UserGuide;
