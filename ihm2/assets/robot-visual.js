import * as THREE from 'https://unpkg.com/three@0.165.0/build/three.module.js';
import { OrbitControls } from 'https://unpkg.com/three@0.165.0/examples/jsm/controls/OrbitControls.js';

export function createRobotVisual(container) {
  const scene = new THREE.Scene();
  scene.background = new THREE.Color(0x0d1722);

  const camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 100);
  camera.position.set(4.5, 3.2, 5.4);

  const renderer = new THREE.WebGLRenderer({ antialias: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(container.clientWidth, container.clientHeight);
  container.appendChild(renderer.domElement);

  const controls = new OrbitControls(camera, renderer.domElement);
  controls.enablePan = false;
  controls.enableDamping = true;
  controls.minDistance = 4;
  controls.maxDistance = 9;
  controls.target.set(0, 1.2, 0);

  scene.add(new THREE.HemisphereLight(0xffffff, 0x1e2a36, 1.6));
  const dirLight = new THREE.DirectionalLight(0xffffff, 1.1);
  dirLight.position.set(4, 6, 4);
  scene.add(dirLight);

  const ground = new THREE.Mesh(
    new THREE.CylinderGeometry(1.8, 1.8, 0.2, 48),
    new THREE.MeshStandardMaterial({ color: 0x163246, metalness: 0.25, roughness: 0.8 })
  );
  ground.position.y = -0.1;
  scene.add(ground);

  const basePivot = new THREE.Group();
  scene.add(basePivot);

  const base = new THREE.Mesh(
    new THREE.CylinderGeometry(0.55, 0.68, 0.65, 36),
    new THREE.MeshStandardMaterial({ color: 0x2ec4b6, metalness: 0.35, roughness: 0.45 })
  );
  base.position.y = 0.32;
  basePivot.add(base);

  const shoulderPivot = new THREE.Group();
  shoulderPivot.position.set(0, 0.68, 0);
  basePivot.add(shoulderPivot);

  const arm1 = new THREE.Mesh(
    new THREE.BoxGeometry(0.36, 1.75, 0.36),
    new THREE.MeshStandardMaterial({ color: 0xf1f5f9, metalness: 0.15, roughness: 0.5 })
  );
  arm1.position.y = 0.88;
  shoulderPivot.add(arm1);

  const elbowPivot = new THREE.Group();
  elbowPivot.position.set(0, 1.72, 0);
  shoulderPivot.add(elbowPivot);

  const arm2 = new THREE.Mesh(
    new THREE.BoxGeometry(0.3, 1.45, 0.3),
    new THREE.MeshStandardMaterial({ color: 0x9fd3ff, metalness: 0.15, roughness: 0.48 })
  );
  arm2.position.y = 0.72;
  elbowPivot.add(arm2);

  const wristPivot = new THREE.Group();
  wristPivot.position.set(0, 1.4, 0);
  elbowPivot.add(wristPivot);

  const wrist = new THREE.Mesh(
    new THREE.BoxGeometry(0.24, 0.8, 0.24),
    new THREE.MeshStandardMaterial({ color: 0xffc857, metalness: 0.15, roughness: 0.48 })
  );
  wrist.position.y = 0.38;
  wristPivot.add(wrist);

  const gripperPivot = new THREE.Group();
  gripperPivot.position.set(0, 0.78, 0);
  wristPivot.add(gripperPivot);

  const palm = new THREE.Mesh(
    new THREE.BoxGeometry(0.28, 0.16, 0.55),
    new THREE.MeshStandardMaterial({ color: 0xff9f1c, metalness: 0.1, roughness: 0.52 })
  );
  gripperPivot.add(palm);

  const leftFingerPivot = new THREE.Group();
  leftFingerPivot.position.set(0, 0, 0.19);
  gripperPivot.add(leftFingerPivot);

  const rightFingerPivot = new THREE.Group();
  rightFingerPivot.position.set(0, 0, -0.19);
  gripperPivot.add(rightFingerPivot);

  const fingerGeometry = new THREE.BoxGeometry(0.08, 0.55, 0.08);
  const fingerMaterial = new THREE.MeshStandardMaterial({ color: 0xffbf69, metalness: 0.08, roughness: 0.55 });

  const leftFinger = new THREE.Mesh(fingerGeometry, fingerMaterial);
  leftFinger.position.set(0.08, -0.24, 0);
  leftFingerPivot.add(leftFinger);

  const rightFinger = new THREE.Mesh(fingerGeometry, fingerMaterial);
  rightFinger.position.set(0.08, -0.24, 0);
  rightFingerPivot.add(rightFinger);

  const box = new THREE.Mesh(
    new THREE.BoxGeometry(0.55, 0.55, 0.55),
    new THREE.MeshStandardMaterial({ color: 0xb08968, roughness: 0.82 })
  );
  box.position.set(2.1, 0.28, 0);
  scene.add(box);

  const animate = () => {
    controls.update();
    renderer.render(scene, camera);
    requestAnimationFrame(animate);
  };
  animate();

  const resize = () => {
    const width = container.clientWidth;
    const height = container.clientHeight;
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    renderer.setSize(width, height);
  };
  window.addEventListener('resize', resize);

  const applyAngles = (angles = {}) => {
    const s6 = Number(angles.servo_6 ?? 40);
    const s7 = Number(angles.servo_7 ?? 130);
    const s8 = Number(angles.servo_8 ?? 180);
    const s10 = Number(angles.servo_10 ?? 0);
    const s11 = Number(angles.servo_11 ?? 120);

    basePivot.rotation.y = THREE.MathUtils.degToRad((s6 - 40) * 0.9);
    shoulderPivot.rotation.z = THREE.MathUtils.degToRad((130 - s7) * 0.55);
    elbowPivot.rotation.z = THREE.MathUtils.degToRad((180 - s8) * 0.45);
    wristPivot.rotation.z = THREE.MathUtils.degToRad((s10 - 10) * 0.4);
    gripperPivot.rotation.x = THREE.MathUtils.degToRad((120 - s11) * 0.18);

    const gripOpen = THREE.MathUtils.clamp((s11 - 50) / 120, 0.02, 0.65);
    leftFingerPivot.rotation.y = gripOpen;
    rightFingerPivot.rotation.y = -gripOpen;

    const boxOffset = THREE.MathUtils.mapLinear(s6, 40, 160, 2.1, 1.2);
    box.position.x = boxOffset;
  };

  applyAngles();

  return { applyAngles };
}
