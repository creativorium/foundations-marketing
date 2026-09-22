import Editor from './Editor.jsx';
export default function createEdit(metadata) {
  return function Edit(props) { return <Editor {...props} metadata={metadata} />; };
}
