import { NoticeList } from "@wordpress/components";
import { useDispatch, useSelect } from "@wordpress/data";
import { store as noticesStore } from "@wordpress/notices";
import { useEffect } from "react";

const Notices = () => {
  const { removeNotice } = useDispatch(noticesStore);
  const notices = useSelect((select) => select(noticesStore).getNotices());

  useEffect(() => {
    if (notices.length > 0) {
      const lastNotice = notices[notices.length - 1];
      const noticeId = lastNotice.id;

      const timeout = setTimeout(() => {
        removeNotice(noticeId);
      }, 3000);

      return () => clearTimeout(timeout);
    }
  }, [notices, removeNotice]);

  if (notices.length === 0) {
    return null;
  }

  return <NoticeList notices={notices} onRemove={removeNotice} />;
};

export { Notices };
