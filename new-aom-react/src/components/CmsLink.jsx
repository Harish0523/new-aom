import { Link } from 'react-router-dom';

export default function CmsLink({ to = '/', children, ...props }) {
  if (/^https?:\/\//i.test(to)) {
    return <a href={to} target="_blank" rel="noreferrer" {...props}>{children}</a>;
  }
  return <Link to={to} {...props}>{children}</Link>;
}
